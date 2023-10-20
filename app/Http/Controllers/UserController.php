<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\TeamShowResource;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user.access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $id = auth()->user()->team_id;

        $team = Team::findOrFail($id)->load('members');

        return Inertia::render('User/Index', [
            'data' => [
                'team'  => new TeamShowResource($team),
            ],
            'meta' => [

            ],
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('user.create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return Inertia::render('User/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $user = auth()->user()->team->members()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->roles) {
            $user->roles()->sync($request->roles);
        }

        return Redirect::route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user.edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return Inertia::render('User/Edit', [
            'user' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->fill($request->only(['name', 'email']));

        $user->roles()->sync($request->roles);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return Redirect::back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user.delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return Redirect::route('users.index')->with('success', 'User deleted.');
    }

    public function restore(User $user)
    {
        abort_if(Gate::denies('user.restore'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->restore();

        return Redirect::back()->with('success', 'User restored.');
    }

    public function invite(Request $request)
    {
        abort_if(Gate::denies('user.invite'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate(['email' => 'email']);
        $team    = Team::where('owner_id', auth()->user()->id)->first();
        $url     = URL::signedRoute('register', ['team' => $team->id]);
        $message = new \App\Notifications\UserInvite($url);
        Notification::route('mail', $request->input('email'))->notify($message);

        return redirect()->back()->with('message', 'Invite sent.');
    }
}