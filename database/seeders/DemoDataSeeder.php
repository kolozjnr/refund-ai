<?php
namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Avery Johnson','Morgan Chen','Jordan Williams','Taylor Okafor','Riley Patel','Casey Martin','Quinn Davis','Jamie Adeyemi','Cameron Brooks','Skylar Nguyen','Parker Thompson','Reese Ibrahim','Rowan Clark','Alex Rivera','Emerson Wilson'];
        $now = now();
        $customerRows = [];
        foreach ($names as $i => $name) $customerRows[] = ['email'=>'customer'.($i + 1).'@example.test','name'=>$name,'phone'=>'+1-555-'.str_pad((string)(1000+$i),4,'0',STR_PAD_LEFT),'created_at'=>$now,'updated_at'=>$now];
        Customer::upsert($customerRows, ['email'], ['name','phone','updated_at']);
        $customers = Customer::whereIn('email', array_column($customerRows, 'email'))->orderBy('id')->get();
        if (Order::exists()) return;
        $specs = [
            ['eligible-damaged',0,2,'delivered',89.99,'Wireless Headphones',false,'damaged'], ['eligible-incorrect',1,3,'delivered',129.00,'Ceramic Pour-over Set',false,'incorrect'], ['final-sale',2,1,'delivered',74.50,'Archive Graphic Tee',true,null], ['expired',3,48,'delivered',219,'Smart Home Speaker',false,null], ['high-value',4,3,'delivered',899,'Studio Monitor Pair',false,null], ['prompt-injection',5,4,'delivered',165,'Mechanical Keyboard',false,null], ['conflict',6,2,'cancelled',280,'Travel Backpack',false,null], ['normal-eligible',7,5,'delivered',48,'Insulated Bottle',false,null], ['normal-denied',8,3,'delivered',115,'Linen Duvet Cover',false,null],
        ];
        foreach ($specs as [$slug,$customerIndex,$days,$status,$amount,$product,$finalSale,$itemStatus]) {
            $scenarioOrders[] = ['customer_id'=>$customers[$customerIndex]->id,'order_number'=>'WN-'.strtoupper($slug),'order_date'=>Carbon::today()->subDays($days)->toDateString(),'status'=>$status,'total_amount'=>$amount,'currency'=>'USD','created_at'=>$now,'updated_at'=>$now,'product_name'=>$product,'quantity'=>1,'unit_price'=>$amount,'final_sale'=>$finalSale,'item_status'=>$itemStatus];
        }
        for ($i=9; $i<15; $i++) { $amount=45 + ($i * 11); $scenarioOrders[]=['customer_id'=>$customers[$i]->id,'order_number'=>'WN-DEMO-'.str_pad((string)($i+1),3,'0',STR_PAD_LEFT),'order_date'=>Carbon::today()->subDays(($i%12)+1)->toDateString(),'status'=>'delivered','total_amount'=>$amount,'currency'=>'USD','created_at'=>$now,'updated_at'=>$now,'product_name'=>['Everyday Canvas Tote','Desk Organizer','Cotton Throw Blanket'][$i%3],'quantity'=>1,'unit_price'=>$amount,'final_sale'=>false,'item_status'=>null]; }
        $orderRows = array_map(fn ($row) => collect($row)->only(['customer_id','order_number','order_date','status','total_amount','currency','created_at','updated_at'])->all(), $scenarioOrders);
        Order::upsert($orderRows, ['order_number'], ['customer_id','order_date','status','total_amount','currency','updated_at']);
        $orders = Order::whereIn('order_number', array_column($orderRows, 'order_number'))->get()->keyBy('order_number');
        $itemRows = [];
        foreach ($scenarioOrders as $row) { $itemRows[]=['order_id'=>$orders[$row['order_number']]->id,'product_name'=>$row['product_name'],'quantity'=>$row['quantity'],'unit_price'=>$row['unit_price'],'final_sale'=>$row['final_sale'],'item_status'=>$row['item_status'],'created_at'=>$now,'updated_at'=>$now]; }
        foreach (array_chunk($itemRows, 100) as $chunk) OrderItem::upsert($chunk, ['order_id','product_name'], ['quantity','unit_price','final_sale','item_status','updated_at']);
    }
}
