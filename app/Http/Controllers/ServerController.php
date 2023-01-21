<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ivampiresp\Cocoa\Http\Controller;
use ivampiresp\Cocoa\Models\Server;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $servers = Server::simplePaginate(10);

        return view('servers.index', compact('servers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'fqdn' => 'required',
            'port' => 'required',
            'username' => 'nullable',
            'password' => 'nullable',
            'status' => 'required',
        ]);

        Server::create($request->all());

        return redirect()->route('servers.index')->with('success', '服务器成功添加。');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('servers.create');
    }

    /**
     * Display the specified resource.
     *
     * @param Server $server
     *
     * @return View
     */
    public function show(Server $server): View
    {
        return view('Cocoa::servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Server $server
     *
     * @return View
     */
    public function edit(Server $server): View
    {
        return view('Cocoa::servers.edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Server  $server
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        //
        // $request->validate([
        //     'name' => 'required',
        //     'fqdn' => 'required',
        //     'port' => 'required',
        //     'status' => 'required',
        // ]);
        $server->update($request->all());

        return back()->with('success', '服务器成功更新。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Server $server
     *
     * @return RedirectResponse
     */
    public function destroy(Server $server): RedirectResponse
    {
        //
        $server->delete();

        return redirect()->route('servers.index')->with('success', '服务器成功删除。');
    }
}
