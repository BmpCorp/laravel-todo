<?php

namespace App\Http\Controllers;

use App\Todo;
use Dotenv\Exception\ValidationException;
use http\Env\Response;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    private function getAllTodos()
    {
        $todos = Todo::all([
            'id', 'name', 'completed'
        ]);

        return new \Illuminate\Http\Response($todos->jsonSerialize(), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->getAllTodos();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:1|max:255',
                'completed' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return new \Illuminate\Http\Response('wrong parameters', 400);
        }

        $todo = new Todo();
        $todo->name = $request->get('name');
        $todo->completed = $request->get('completed') === '1';
        $todo->save();

        return $this->getAllTodos();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer',
                'name' => 'nullable|min:1|max:255',
                'completed' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return new \Illuminate\Http\Response('wrong parameters', 400);
        }

        $todo = Todo::find(intval($request->get('id')));
        if (!$todo) {
            return new \Illuminate\Http\Response('no such record', 404);
        }

        $fields = [];
        if ($request->get('name') !== null) {
            $fields['name'] = $request->get('name');
        }
        if ($request->get('completed') !== null) {
            $fields['completed'] = ($request->get('completed') === '1');
        }

        $todo->update($fields);

        return $this->getAllTodos();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return new \Illuminate\Http\Response('wrong parameters', 400);
        }

        $todo = Todo::find(intval($request->get('id')));
        if (!$todo) {
            return new \Illuminate\Http\Response('no such record', 404);
        }

        try {
            $todo->delete();
        } catch (\Exception $exception) {
            return new \Illuminate\Http\Response("can't delete todo $todo->id", 400);
        }

        return $this->getAllTodos();
    }
}
