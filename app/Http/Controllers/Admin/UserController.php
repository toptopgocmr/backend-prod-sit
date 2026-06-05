<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function index() { return view('users.index', ['users'=>User::withTrashed()->orderBy('role')->get()]); }
    public function create() { return view('users.create'); }
    public function store(Request $request) {
        $validated = $request->validate(['name'=>'required|string|max:150','email'=>'required|email|unique:users','phone'=>'nullable|string','role'=>'required|in:admin,couturier,stock_manager,cashier,delivery','password'=>'required|string|min:8|confirmed']);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('users.index')->with('success','Utilisateur créé.');
    }
    public function show(User $user) { return view('users.show', compact('user')); }
    public function edit(User $user) { return view('users.edit', compact('user')); }
    public function update(Request $request, User $user) {
        $validated = $request->validate(['name'=>'required|string','email'=>'required|email|unique:users,email,'.$user->id,'role'=>'required|in:admin,couturier,stock_manager,cashier,delivery']);
        if ($request->filled('password')) { $validated['password'] = Hash::make($request->password); }
        $user->update($validated);
        return redirect()->route('users.index')->with('success','Utilisateur mis à jour.');
    }
    public function toggle(User $user) {
        if ($user->id === auth()->id()) return back()->with('error','Impossible de vous désactiver vous-même.');
        $user->update(['is_active'=>!$user->is_active]);
        return back()->with('success','Compte '.($user->is_active ? 'activé' : 'désactivé').'.');
    }
    public function destroy(User $user) { $user->delete(); return redirect()->route('users.index'); }
}
