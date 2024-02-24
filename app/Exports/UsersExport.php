<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

final class UsersExport implements FromView
{
    public function view(): View
    {
        $users = User::with(relations: 'agent')->get()->toArray();

        foreach ($users as $key => $user) {
            $userModel = User::find(id: $user['id']);
            $idLocations = 0;
            foreach ($userModel->locations as $location) {
                $users[$key]['locations'][$idLocations]['type']= $location->type->value;
                $users[$key]['locations'][$idLocations]['firstname']= $location->firstname;
                $users[$key]['locations'][$idLocations]['lastname']= $location->lastname;
                $users[$key]['locations'][$idLocations]['company']= $location->company;
                $users[$key]['locations'][$idLocations]['vat']= $location->vat;
                $users[$key]['locations'][$idLocations]['street']= $location->street;
                $users[$key]['locations'][$idLocations]['street_number']= $location->street_number;
                $users[$key]['locations'][$idLocations]['street_other']= $location->street_other;
                $users[$key]['locations'][$idLocations]['zip']= $location->zip;
                $users[$key]['locations'][$idLocations]['city']= $location->city;
                $users[$key]['locations'][$idLocations]['country']= $location->country->value;
                $users[$key]['locations'][$idLocations]['phone']= $location->phone;
                $idLocations++;
            }
            $users[$key]['qtyLocations']= $idLocations;
            $idBrands = 0;
            foreach ($userModel->brands as $brand) {
                $users[$key]['brands'][$idBrands]['brand']= $brand->brand->name;
                $users[$key]['brands'][$idBrands]['category']= ($brand->category) ? $brand->category->name : null;
                $users[$key]['brands'][$idBrands]['category_meta']= ($brand->category_meta) ? $brand->category_meta->name : null;
                $users[$key]['brands'][$idBrands]['category_meta_value']= $brand->category_meta_value;
                $users[$key]['brands'][$idBrands]['reduction']= $brand->reduction;
                $users[$key]['brands'][$idBrands]['coefficient']= $brand->coefficient;
                $users[$key]['brands'][$idBrands]['addition_price']= $brand->addition_price;
                $users[$key]['brands'][$idBrands]['price_type']= $brand->price_type;
                $users[$key]['brands'][$idBrands]['not_show_promo']= $brand->not_show_promo;
                $users[$key]['brands'][$idBrands]['is_excluded']= $brand->is_excluded;
                $idBrands++;
            }
            $users[$key]['qtyBrands']= $idBrands;
        }

        return view(view: 'exports.users', data: [
            'users' => $users,
        ]);
    }
}
