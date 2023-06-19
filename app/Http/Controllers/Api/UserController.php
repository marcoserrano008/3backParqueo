<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use DateTime;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return UserResource::collection(User::query()->orderBy('id', 'desc')->paginate(10));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        return response(new UserResource($user) , 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if(isset($data['password'])){
            $data['password'] = bcrypt($data['password']);
        };
        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response("", 204);
    }

    public function getClientsByDate($start_date, $end_date)
    {
        $users = User::whereBetween('created_at', [$start_date, $end_date])
                     ->where('rol', 'cliente')
                     ->get();
        
        $result = [];
        foreach ($users as $user) {
            $dateOfBirth = new DateTime($user->fecha_nacimiento);
            $now = new DateTime();
            $interval = $dateOfBirth->diff($now);

            $result[] = [
                'id_cliente' => $user->id,
                'nombre' => $user->name.' '.$user->apellido_paterno.' '.$user->apellido_materno,
                'email' => $user->email,
                'ci' => $user->ci,
                'edad' => $interval->y
            ];
        }

        return response()->json($result);
    }

    public function listAllUsers (){
        $usuarios = User::get();
        return $usuarios;
    }
}
