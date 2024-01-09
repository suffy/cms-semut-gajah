<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PartnerLogo;

class PartnerLogoController extends Controller
{

    protected $partnerlogo;

    public function __construct(PartnerLogo $partnerlogo) {
        $this->partnerlogo = $partnerlogo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        

        $partner_logo = new $this->partnerlogo;
        $partner_logo = $partner_logo ->orderBy('id','desc');

        if ($request->has('search')) {
            $partner_logo = $partner_logo->where('name', 'like','%'.$request->search.'%');
        }

        if ($request->has('type')) {

            if($request->type=='vendor'){
                $partner_logo = $partner_logo->where('type', $request->type);
            }else{
                return redirect(url('admin/partner-logo?type=vendor'));
            }

        }else{
            return redirect(url('admin/partner-logo?type=vendor'));
        }

        $partner_logo = $partner_logo->paginate(10);

        return view('admin.pages.partner-logo')
            ->with('partner_logo', $partner_logo);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        $partner_logo = new $this->partnerlogo;
        $partner_logo->name = $request->name;
        $partner_logo->slug = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u','', strip_tags(strtolower(str_replace(" ", "-", $request->input('name')))))));
        $partner_logo->url  = $request->link;
        $partner_logo->type  = $request->type;
        $partner_logo->content  = $request->content;
        $partner_logo->status = 1;

        if($request->hasFile('icon'))
        {
            // image's folder
            $folder = 'partner_logo';
            // image's filename
            $newName = "partner_logo-" . date('Ymd-His');
            // image's form field name
            $form_name = 'icon';

            $partner_logo->icon = \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $partner_logo->save();

        return redirect($request->input('url_redirect'))
        ->with('status', 1)
        ->with('message', "Data Saved!");
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
        $partner_logo = $this->partnerlogo->find($id);

        if ($partner_logo) {
            $partner_logo->name = $request->input('name');
            $partner_logo->type = $request->input('type');
            $partner_logo->content = $request->input('content');
            $partner_logo->slug = strtolower(str_replace(" ", "-", $request->input('name')));
            $partner_logo->url  = $request->input('link');
            $partner_logo->status  = $request->input('status');
            if($request->hasFile('icon'))
            {
                $folder     = 'partner_logo'; // image's folder
                $newName    = "partner_logo-" . date('Ymd-His'); // image's filename
                $form_name  = 'icon'; // image's form field name

                if (file_exists($partner_logo->icon)) {
                    unlink($partner_logo->icon); //delete old file
                }
                $partner_logo->icon = \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
            }

            $partner_logo->save();

            return redirect($request->input('url_redirect'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
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
        $partner_logo = $this->partnerlogo->find($id);
        if (isset($partner_logo)) {

            if (file_exists($partner_logo->icon)) {
                unlink($partner_logo->icon); //delete old file
            }
            $partner_logo->delete();
        }

        return redirect('admin/partner-logo')
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }

    public function changeStatus(Request $request)
    {
        $partner_logo = SocialMedia::find($request->partner_logo_id);
        $partner_logo->status = $request->input('status');
        $partner_logo->save();

        return response()->json(['success'=>'Status change successfully.']);
    }
}
