<?php

namespace App\Http\Controllers\Helpers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected $model;
    protected $modelName;
    protected $anonymous = ['lookup', 'signup'];

    public function __construct(Model $modelo)
    {
        $this->model = $modelo;
        $this->modelName = get_class($modelo);
        $this->middleware('auth:api', ['except' => $this->anonymous]);
    }

    private function parseParameter(Builder $query, string $field, string $value)
    {
        $args = array_map('trim', explode("__", $field));
        $field = $args[0];

        if (count($args) == 1) {
            $query->where($field, $value);
        } else {
            if (in_array("isnull", $args)) {
                if ($value)
                    $query->whereNull($field);
                else
                    $query->whereNotNull($field);
            } else if (in_array("in", $args)) {
                $query->whereIn($field, explode(",", $value));
            } else {
                $table = $args[0];

                array_shift($args);
                $args = array_values($args);

                $query->whereHas($table, function (Builder $q) use ($args, $value) {
                    if (count($args) == 1)
                        $q->where($args[0], $value);
                    else
                        $this->parseParameter($q, $args[0], $value);
                });
            }
        }
    }

    public function get_query(Request $request)
    {
        $query = $this->model::query();

        $todosOsParametros = $request->query();
        foreach ($todosOsParametros as $campo => $valor) {
            if (in_array($campo, ["page", "pagesize"]))
                continue;

            $this->parseParameter($query, $campo, $valor);
        }

        $query = $query->with($this->model->with_includes());
        return $query;
    }

    public function index(Request $request)
    {
        $data = $this->get_query($request);
        if ($request->has("page")) {
            $page = $request->input("page", 1);
            $pagesize = $request->input("pagesize", 10);

            $result = $data->paginate($pagesize, ["*"], "page", $page);
            if ($result->currentPage() > $result->lastPage()) {
                $result = $data->paginate($pagesize, ["*"], "page", 1);
            }

            return response()->json($result);
        }

        return response()->json($data->get());
    }

    public function lookup(Request $request)
    {
        $queryResult = $this->get_query($request)->get();
        $lookup = $queryResult->map(function ($item) {
            return ["value" => $item->id, "display" => $item->__toString()];
        });

        return response()->json($lookup);
    }

    public function show(Request $request, $id)
    {
        $recurso = $this->get_query($request)->find($id);

        if (!$recurso) {
            return response()->json(['mensagem' => 'Recurso não encontrado'], 404);
        }

        return response()->json($recurso);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $this->model->transformData($request);
            $validator = Validator::make($request->all(), $this->model->getRules());

            if ($validator->fails()) {
                return response()->json(['erros' => $validator->errors()], 400);
            }

            $recurso = $this->model->create($request->all());
            $recurso->afterSave();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, $recurso->hasOne)) {
                    $recurso->{$key}()->create($request->input($key));
                } else if (in_array($key, $recurso->hasMany)) {
                    $recurso->{$key}()->createMany($request->input($key));
                }
            }

            DB::commit();
            return response()->json($recurso, 201);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['erro' => 'Erro ao salvar itens. Detalhes: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $this->model->transformData($request);
            $recurso = $this->get_query($request)->find($id);

            if (!$recurso) {
                return response()->json(['mensagem' => 'Recurso não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), $this->model->getRules());

            if ($validator->fails()) {
                return response()->json(['erros' => $validator->errors()], 400);
            }

            $recurso->beforeSave();
            $recurso->update($request->all());
            $recurso->afterSave();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, $recurso->hasOne)) {
                    $recurso->{$key}()->create($request->input($key));
                } else if (in_array($key, $recurso->hasMany)) {
                    $recurso->{$key}()->createMany($request->input($key));
                }
            }

            DB::commit();
            return response()->json($recurso);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['erro' => 'Erro ao salvar itens. Detalhes: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $recurso = $this->get_query($request)->find($id);

            if (!$recurso) {
                return response()->json(['mensagem' => 'Recurso não encontrado'], 404);
            }

            $recurso->delete();
            DB::commit();
            return response()->json(['mensagem' => 'Recurso removido com sucesso']);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['erro' => 'Erro ao salvar itens. Detalhes: ' . $e->getMessage()], 500);
        }
    }

    public function bulk(Request $request)
    {
        try {
            DB::beginTransaction();
            $errors = [];
            $response = [];

            foreach ($request->all() as $key => $item) {
                $this->model->transformData($request);
                $validator = Validator::make($item, $this->model->getRules());
                if ($validator->fails()) {
                    $errors[] = [
                        "dados" => $item,
                        "errors" => $validator->errors()->toArray(),
                    ];
                }
            }

            if (!empty($errors))
                return response()->json(['erros' => $errors], 400);

            foreach ($request->all() as $key => $item) {
                if (isset($item['id']) && $item['id'] > 0) {
                    $recurso = $this->model->find($item['id']);

                    if ($recurso) {
                        $recurso->beforeSave();
                        $recurso->update($item);
                        $recurso->afterSave();
                        $response[] = $recurso;
                    }
                } else {
                    $response[] = $this->model->create($item);
                }
            }

            DB::commit();
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['erro' => 'Erro ao salvar itens. Detalhes: ' . $e->getMessage()], 500);
        }
    }
}
