<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        // return $user;
        return view('users.show', compact('user'));
    }

    /**
    *   1.将用户提交的信息存储到数据库，并重定向到其个人页面；
    *   2.在网页顶部位置显示注册成功的提示信息；
    */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        // dd($request->all());
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);

        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
    }

    // 编辑个人用户信息
    public function edit(User $user)
    {
        // 这里 update 是指授权类里的 update 授权方法，$user 对应传参 update 授权方法的第二个参数。正如上面定义 update 授权方法时候提起的，调用时，默认情况下，我们 不需要 传递第一个参数，也就是当前登录用户至该方法内，因为框架会自动加载当前登录用户。
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    // 更新个人信息
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    // 删除用户 is_admin
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
