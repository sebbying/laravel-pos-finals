<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Hash,Mail,Storage};
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Models\{User,Product,Customer,Sale};
use App\Mail\SaleReceipt;
class PosTest extends TestCase {
 use RefreshDatabase;
 private function admin(): User {return User::create(['username'=>'admin','full_name'=>'Administrator','password'=>'StrongPass123!','role'=>'admin']);}
 private function product(int $stock=10): Product {return Product::create(['name'=>'Notebook','price'=>'12.35','stock_quantity'=>$stock]);}
 private function saleData(Product $p,array $extra=[]): array {return array_merge(['product_id'=>$p->id,'quantity'=>3,'request_key'=>(string)Str::uuid()],$extra);}
 public function test_management_requires_authentication(): void {
  foreach(['/','/products','/products/create','/customers','/staff','/sales','/sales/create','/email'] as $url) $this->get($url)->assertRedirect('/login');
  $this->post('/sales',[])->assertRedirect('/login');
 }
 public function test_login_and_logout(): void {
  $admin=$this->admin();$this->post('/login',['username'=>'admin','password'=>'wrong'])->assertSessionHasErrors('username');
  $this->post('/login',['username'=>'admin','password'=>'StrongPass123!'])->assertRedirect('/');$this->assertAuthenticatedAs($admin);
  $this->post('/logout')->assertRedirect('/login');$this->assertGuest();
 }
 public function test_all_management_pages_render(): void {
  $admin=$this->admin();$p=$this->product();$c=Customer::create(['full_name'=>'Customer','email'=>'customer@example.com']);$this->actingAs($admin);
  foreach(['/','/products','/products/create',"/products/$p->id", "/products/$p->id/edit",'/customers','/customers/create',"/customers/$c->id", "/customers/$c->id/edit",'/staff','/staff/create',"/staff/$admin->id", "/staff/$admin->id/edit",'/sales','/sales/create','/email'] as $url) $this->get($url)->assertOk();
 }
 public function test_product_crud_and_image_processing(): void {
  Storage::fake('local');$this->actingAs($this->admin());
  $this->post('/products',['name'=>'Pen','price'=>'5.50','stock_quantity'=>20,'image'=>UploadedFile::fake()->image('photo.png',1500,1000)])->assertRedirect(route('products.index'));
  $p=Product::firstOrFail();$this->assertStringEndsWith('.jpg',$p->image);Storage::assertExists($p->image);
  $dimensions=getimagesize(Storage::path($p->image));$this->assertSame(1000,$dimensions[0]);
  $this->get(route('media',basename($p->image)))->assertOk()->assertHeader('Content-Type','image/jpeg');
  $this->put("/products/$p->id",['name'=>'Blue pen','price'=>'6.50','stock_quantity'=>15])->assertRedirect();$this->assertSame('Blue pen',$p->fresh()->name);
  $this->delete("/products/$p->id")->assertRedirect();$this->assertSoftDeleted($p);
 }
 public function test_customer_crud(): void {
  $this->actingAs($this->admin());$this->post('/customers',['full_name'=>'Ana','email'=>'ana@example.com','phone'=>'09123456789'])->assertRedirect();
  $c=Customer::firstOrFail();$this->put("/customers/$c->id",['full_name'=>'Ana Cruz','email'=>'ana@example.com'])->assertRedirect();$this->assertSame('Ana Cruz',$c->fresh()->full_name);
  $this->delete("/customers/$c->id")->assertRedirect();$this->assertSoftDeleted($c);
 }
 public function test_staff_crud_hashing_and_avatar(): void {
  Storage::fake('local');$this->actingAs($this->admin());$this->post('/staff',['username'=>'clerk','full_name'=>'Clerk','role'=>'staff','password'=>'StrongPass456!','password_confirmation'=>'StrongPass456!','avatar'=>UploadedFile::fake()->image('avatar.png')])->assertRedirect();
  $u=User::where('username','clerk')->firstOrFail();$this->assertTrue(Hash::check('StrongPass456!',$u->password));Storage::assertExists($u->avatar);
  $this->put("/staff/$u->id",['username'=>'clerk','full_name'=>'Updated Clerk','role'=>'staff','password'=>''])->assertRedirect();$this->assertTrue(Hash::check('StrongPass456!',$u->fresh()->password));
  $this->delete("/staff/$u->id")->assertRedirect();$this->assertSoftDeleted($u);
 }
 public function test_staff_cannot_manage_users_or_email_settings(): void {
  $u=User::create(['username'=>'clerk','full_name'=>'Clerk','password'=>'StrongPass456!','role'=>'staff']);$this->actingAs($u);
  $this->get('/staff')->assertForbidden();$this->post('/staff',[])->assertForbidden();$this->get('/email')->assertForbidden();
 }
 public function test_admin_cannot_delete_or_demote_self(): void {
  $a=$this->admin();$this->actingAs($a);$this->delete("/staff/$a->id")->assertSessionHasErrors();
  $this->put("/staff/$a->id",['username'=>'admin','full_name'=>'Administrator','role'=>'staff'])->assertSessionHasErrors('role');
 }
 public function test_sale_decrements_stock_and_uses_server_price(): void {
  $a=$this->admin();$p=$this->product();$this->actingAs($a)->post('/sales',$this->saleData($p,['total_price'=>'0.01','sold_by'=>999]))->assertRedirect();
  $s=Sale::firstOrFail();$this->assertSame('37.05',$s->total_price);$this->assertSame($a->id,$s->sold_by);$this->assertSame(7,$p->fresh()->stock_quantity);$this->get("/sales/$s->id")->assertOk();
 }
 public function test_overselling_is_rejected_without_writes(): void {
  $p=$this->product(2);$this->actingAs($this->admin())->post('/sales',$this->saleData($p))->assertSessionHasErrors('quantity');$this->assertDatabaseCount('sales',0);$this->assertSame(2,$p->fresh()->stock_quantity);
 }
 public function test_checkout_cannot_be_submitted_twice(): void {
  $p=$this->product();$data=$this->saleData($p);$this->actingAs($this->admin());$this->post('/sales',$data)->assertRedirect();$this->post('/sales',$data)->assertSessionHasErrors();$this->assertDatabaseCount('sales',1);$this->assertSame(7,$p->fresh()->stock_quantity);
 }
 public function test_zero_negative_and_fractional_quantities_are_rejected(): void {
  $p=$this->product();$this->actingAs($this->admin());foreach([0,-1,'1.5'] as $qty)$this->post('/sales',$this->saleData($p,['quantity'=>$qty]))->assertSessionHasErrors('quantity');$this->assertDatabaseCount('sales',0);
 }
 public function test_sale_history_survives_removed_records_and_price_changes(): void {
  $a=$this->admin();$p=$this->product();$c=Customer::create(['full_name'=>'Ana','email'=>'ana@example.com']);$this->actingAs($a)->post('/sales',$this->saleData($p,['customer_id'=>$c->id]));
  $s=Sale::firstOrFail();$p->update(['name'=>'New name','price'=>'999.99']);$p->delete();$c->delete();$a->delete();
  $s->refresh();$this->assertSame('Notebook',$s->product_name);$this->assertSame('37.05',$s->total_price);$this->assertNotNull($s->product);$this->assertNotNull($s->customer);$this->assertNotNull($s->staff);
 }
 public function test_invalid_upload_is_rejected(): void {
  $this->actingAs($this->admin())->post('/products',['name'=>'Pen','price'=>'2.00','stock_quantity'=>5,'image'=>UploadedFile::fake()->create('script.php',10,'text/plain')])->assertSessionHasErrors('image');
 }
 public function test_receipt_email_is_sent_to_saved_customer(): void {
  Mail::fake();config(['mail.default'=>'smtp']);$p=$this->product();$c=Customer::create(['full_name'=>'Ana','email'=>'ana@example.com']);$this->actingAs($this->admin())->post('/sales',$this->saleData($p,['customer_id'=>$c->id]));$s=Sale::firstOrFail();
  $this->post("/sales/$s->id/email")->assertSessionHasNoErrors();Mail::assertSent(SaleReceipt::class,fn($m)=>$m->hasTo('ana@example.com'));$this->assertNotNull($s->fresh()->receipt_sent_at);
 }
 public function test_failed_email_does_not_undo_sale(): void {
  $p=$this->product();$c=Customer::create(['full_name'=>'Ana','email'=>'ana@example.com']);$this->actingAs($this->admin())->post('/sales',$this->saleData($p,['customer_id'=>$c->id]));$s=Sale::firstOrFail();config(['mail.default'=>'smtp']);
  Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP unavailable'));$this->post("/sales/$s->id/email")->assertSessionHasErrors('email');$this->assertDatabaseCount('sales',1);$this->assertSame(7,$p->fresh()->stock_quantity);
 }
 public function test_log_mode_never_claims_receipt_was_delivered(): void {
  config(['mail.default'=>'log']);$p=$this->product();$c=Customer::create(['full_name'=>'Ana','email'=>'ana@example.com']);$this->actingAs($this->admin())->post('/sales',$this->saleData($p,['customer_id'=>$c->id]));$s=Sale::firstOrFail();$this->post("/sales/$s->id/email")->assertSessionHasErrors('email');$this->assertNull($s->fresh()->receipt_sent_at);
 }
 public function test_archived_product_cannot_be_sold(): void {
  $p=$this->product();$p->delete();$this->actingAs($this->admin())->post('/sales',$this->saleData($p))->assertSessionHasErrors('product_id');$this->assertDatabaseCount('sales',0);
 }
}
