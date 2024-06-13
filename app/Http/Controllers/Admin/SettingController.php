<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;

class SettingController extends Controller
{
    public function getLogo(){
        $data = [];
        $logo = Setting::where('type', 'logo')->first();
        if ($logo) {
            $data['black_logo'] = $logo->image ? Storage::url($logo->image) : null;
            $data['white_logo'] = $logo->content ? Storage::url($logo->content) : null;
        }

        return response()->json(['data' => $data, 'status' => '200', 'message' => 'Logo']);
    }
    public function storeOrUpdateSiteLogs(Request $request)
    {
        $request->validate([
            'black_logo' => 'nullable|image',
            'white_logo' => 'nullable|image',
        ]);

        $logo = Setting::where('type', $request->type)->first(); //type == logo
        $blackLogo = $logo->image ?? null;
        $whiteLogo = $logo->content ?? null;

        if ($request->hasFile('black_logo')) { // Main Black Logo mange
            $blackLogo = $this->storeUpdateSiteLogs($request->file('black_logo'), 'black_logo');
            if (!empty($logo->image) && Storage::exists($logo->image)){
                Storage::delete($logo->image);
            }
        }
        if ($request->hasFile('white_logo')) { // Main White Logo mange
            $whiteLogo = $this->storeUpdateSiteLogs($request->file('white_logo'), 'white_logo');
            if (!empty($logo->content) && Storage::exists($logo->content))
                Storage::delete($logo->content);
        }

        if (!$logo)
            $logo = new Setting();
        $logo->type = $request->type;
        $logo->image = $blackLogo; // Store black logo to settings table 'image' column default.
        $logo->content = $whiteLogo; // Store white logo to settings table 'content_1' column default.

        $logo->save();
    }
    public function deleteLogo(Request $request)
    {
        $logo = Setting::where('type', 'logo')->first();
        if ($logo) {
            $column = $request->column;
            if ($logo && Storage::exists($logo->$column))
                Storage::delete($logo->$column);

            $logo->$column = null;
        }
        $logo->save();
    }
    
    //inside call
    public function storeUpdateSiteLogs($file, $type)
    {
        $filename = \Illuminate\Support\Str::uuid();
        $ext = $file->getClientOriginalExtension();
        if ($type == 'small_logo') {
            $logoStorePath = 'logo/' . $filename . '.webp';
            $makePath = Image::make($file)->encode('webp');
        }elseif (($type == 'meta_default_image')){
            $logoStorePath = 'logo/meta/' . $filename . '.webp';
            $makePath = Image::make($file)->encode('webp');
        }elseif (($type == 'email_template_banner')){
            $logoStorePath = 'logo/email/' . $filename . '.webp';
            $makePath = Image::make($file)->resize(750, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('webp', 100);
        }else {
            $logoStorePath = 'logo/' . $filename . '.' . $ext;
            $makePath = Image::make($file)->encode('png');
        }
        Storage::put($logoStorePath, $makePath);

        return $logoStorePath;
    }
    public function getCountries()
    {
        $countries = Country::orderBy('name')->get();
        return CountryResource::collection($countries);
    }
}
