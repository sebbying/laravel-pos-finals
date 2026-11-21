<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class StaffController {
 public function index(Request $r) { $q=User::query(); $term=mb_substr((string)$r->query('q',''),0,100); if($term!=='') $q->where('full_name','like','%'.$term.'%'); return view('manage.index',['records'=>$q->latest()->paginate(12)->withQueryString(),'entity'=>'staff','title'=>'Staff','term'=>$term]); }
 public function create() {return $this->form(new User);}
 public function show(User $staff) {return view('manage.show',['record'=>$staff,'entity'=>'staff','title'=>'Staff']);}
 public function edit(User $staff) {return $this->form($staff);}
 private function form(User $record) {return view('manage.form',compact('record')+['entity'=>'staff','title'=>'Staff']);}
 public function store(Request $r,ImageService $images) {return $this->save($r,new User,$images);}
 public function update(Request $r,User $staff,ImageService $images) {return $this->save($r,$staff,$images);}
 private function save(Request $r,User $record,ImageService $images) {
  $data=$r->validate(['username'=>['required','alpha_dash','max:50',Rule::unique('users')->ignore($record->id)],'full_name'=>'required|string|max:100','role'=>['required',Rule::in(['admin','staff'])],'password'=>[$record->exists ? 'nullable' : 'required','string','min:10','max:72','confirmed'],'avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000']);
  if($record->id === $r->user()->id && $data['role'] !== 'admin') throw ValidationException::withMessages(['role'=>'You cannot remove your own administrator access.']);
  if(empty($data['password'])) unset($data['password']);
  if($r->hasFile('avatar')) $data['avatar']=$images->store($r->file('avatar'));
  $record->fill($data)->save();return redirect()->route('staff.index')->with('status','Staff saved.');
 }
 public function destroy(User $staff) { if($staff->id === auth()->id()) return back()->withErrors(['staff'=>'You cannot delete your own account.']); $staff->delete();return redirect()->route('staff.index')->with('status','Staff removed. Transaction history retained.'); }
}
