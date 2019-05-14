<?php

namespace App\Http\Controllers;

use App\Todo;
use Dotenv\Exception\ValidationException;
use http\Env\Response;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use stdClass;

class TodoController extends Controller
{
    private function makeResponse($status = 200, $statusMessage = null) {
        $response = new StdClass();
        $response->todos = Todo::all(['id', 'name', 'completed'])->toArray();

        if ($statusMessage !== null) {
            $response->status = $statusMessage;
        }

        return new \Illuminate\Http\Response(json_encode($response), $status, [
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
        return $this->makeResponse();
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
            return $this->makeResponse(400, 'wrong parameters');
        }

        $todo = new Todo();
        $todo->name = $request->get('name');
        $todo->completed = $request->get('completed') === '1';
        $todo->save();

        return $this->makeResponse(200, 'ok');
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
            return $this->makeResponse(400, 'wrong parameters');
        }

        $todo = Todo::find(intval($request->get('id')));
        if (!$todo) {
            return $this->makeResponse(404, 'no such record');
        }

        $fields = [];
        if ($request->get('name') !== null) {
            $fields['name'] = $request->get('name');
        }
        if ($request->get('completed') !== null) {
            $fields['completed'] = ($request->get('completed') === '1');
        }

        $todo->update($fields);

        return $this->makeResponse(200, 'ok');
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
            return $this->makeResponse(400, 'wrong parameters');
        }

        $todo = Todo::find(intval($request->get('id')));
        if (!$todo) {
            return $this->makeResponse(404, 'no such record');
        }

        try {
            $todo->delete();
        } catch (\Exception $exception) {
            return $this->makeResponse(400, "can't delete todo $todo->id");
        }

        return $this->makeResponse(200, 'ok');
    }
}
