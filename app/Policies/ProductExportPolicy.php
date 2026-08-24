<?php
namespace App\Policies; use App\Models\{ProductExport,User}; class ProductExportPolicy { public function before(User $user):?bool{return $user->is_admin?true:null;} public function viewAny(User $user):bool{return $user->is_admin;} public function export(User $user):bool{return $user->is_admin;} }
