<?php
namespace App\Policies; use App\Models\{ProductImport,User}; class ProductImportPolicy { public function before(User $user):?bool{return $user->is_admin?true:null;} public function viewAny(User $user):bool{return $user->is_admin;} public function import(User $user):bool{return $user->is_admin;} }
