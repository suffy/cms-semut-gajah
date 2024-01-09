<?php

namespace App\Http\Controllers\Admin;

use App\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as InterImage;

class MenuController extends Controller
{
    protected $menu;

    public function __construct(Menu $menu) {
        $this->menu = $menu;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menu = $this->menu->paginate(10);
        return view('admin.pages.menu', compact('menu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id){
        $menu = $this->menu->find($id);

        if($menu){
            $menu->status = $request->input('status');
            $menu->save();
            return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
            ->with('status', 2)
            ->with('message', "Error while save data!");
        }

    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $slug = strtolower(str_replace(" ", "-", $request->input('name')));
        $data_menu = Menu::create([
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'slug' => $slug,
            'menu_order' => '0',
            'editable' => '0',
            'deletable' => '0',
            'status' => '1',
        ]);
        
        if ($data_menu) {

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $menu = $this->menu->find($request->input('id'));

        if ($menu) {

                $menu->name = $request->input('name');
                $menu->name_en = $request->input('name_en');
                $menu->status = $request->input('status');
                $menu->slug = strtolower(str_replace(" ", "-", $request->input('name')));

                $menu->save();

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");

        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
        }
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $menu = $this->menu->find($id);
        if (isset($menu)) {
            $menu->delete();
        }

        return redirect('admin/menus')
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }
}
