<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->seedCustomers();
        $verifiedItems = $this->seedDemoOrders($customers);
        $this->seedProductReviews($customers, $verifiedItems);
    }

    private function seedCustomers(): Collection
    {
        $definitions = [
            ['name' => 'Nusrat Jahan', 'email' => 'customer@storez.local', 'phone' => '01710001001'],
            ['name' => 'Rafiq Ahmed', 'email' => 'rafiq.ahmed@storez.local', 'phone' => '01810001002'],
            ['name' => 'Tania Rahman', 'email' => 'tania.rahman@storez.local', 'phone' => '01910001003'],
            ['name' => 'Farhan Kabir', 'email' => 'farhan.kabir@storez.local', 'phone' => '01610001004'],
            ['name' => 'Maliha Sultana', 'email' => 'maliha.sultana@storez.local', 'phone' => '01710001005'],
            ['name' => 'Imran Hossain', 'email' => 'imran.hossain@storez.local', 'phone' => '01810001006'],
            ['name' => 'Sadia Islam', 'email' => 'sadia.islam@storez.local', 'phone' => '01910001007'],
            ['name' => 'Mehedi Hasan', 'email' => 'mehedi.hasan@storez.local', 'phone' => '01610001008'],
            ['name' => 'Ayesha Akter', 'email' => 'ayesha.akter@storez.local', 'phone' => '01710001009'],
            ['name' => 'Tanvir Chowdhury', 'email' => 'tanvir.chowdhury@storez.local', 'phone' => '01810001010'],
            ['name' => 'Sharmeen Akter', 'email' => 'sharmeen.akter@storez.local', 'phone' => '01910001011'],
            ['name' => 'Arif Mahmud', 'email' => 'arif.mahmud@storez.local', 'phone' => '01610001012'],
            ['name' => 'Samira Khan', 'email' => 'samira.khan@storez.local', 'phone' => '01710001013'],
            ['name' => 'Fahim Rahman', 'email' => 'fahim.rahman@storez.local', 'phone' => '01810001014'],
            ['name' => 'Tahmina Yasmin', 'email' => 'tahmina.yasmin@storez.local', 'phone' => '01910001015'],
            ['name' => 'Nabil Hasan', 'email' => 'nabil.hasan@storez.local', 'phone' => '01610001016'],
            ['name' => 'Jannatul Ferdous', 'email' => 'jannatul.ferdous@storez.local', 'phone' => '01710001017'],
            ['name' => 'Sabbir Hossain', 'email' => 'sabbir.hossain@storez.local', 'phone' => '01810001018'],
            ['name' => 'Mim Akter', 'email' => 'mim.akter@storez.local', 'phone' => '01910001019'],
            ['name' => 'Rakibul Islam', 'email' => 'rakibul.islam@storez.local', 'phone' => '01610001020'],
            ['name' => 'Fariha Ahmed', 'email' => 'fariha.ahmed@storez.local', 'phone' => '01710001021'],
            ['name' => 'Adnan Karim', 'email' => 'adnan.karim@storez.local', 'phone' => '01810001022'],
            ['name' => 'Nabila Sultana', 'email' => 'nabila.sultana@storez.local', 'phone' => '01910001023'],
            ['name' => 'Mahin Chowdhury', 'email' => 'mahin.chowdhury@storez.local', 'phone' => '01610001024'],
        ];

        return collect($definitions)->map(fn (array $data) => User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make('change-me-local'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        ));
    }

    /** @return array<int, array<int, int>> */
    private function seedDemoOrders(Collection $customers): array
    {
        $products = Product::query()
            ->with(['defaultVariant', 'variants'])
            ->published()
            ->orderBy('id')
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        $definitions = [
            ['number' => 'SZ-DEMO-PENDING', 'status' => 'pending', 'payment_status' => 'unpaid', 'product' => $products[0], 'customer' => $customers[0]],
            ['number' => 'SZ-DEMO-COMPLETED', 'status' => 'completed', 'payment_status' => 'paid', 'product' => $products[1] ?? $products[0], 'customer' => $customers[1]],
            ['number' => 'SZ-DEMO-CANCELLED', 'status' => 'cancelled', 'payment_status' => 'unpaid', 'product' => $products[2] ?? $products[0], 'customer' => $customers[2]],
        ];

        $verifiedItems = [];

        foreach ($definitions as $definition) {
            $product = $definition['product'];
            $customer = $definition['customer'];
            $variant = $product->defaultVariant ?: $product->variants->first();

            if (! $variant) {
                continue;
            }

            $price = $variant->currentPriceMinor();
            $order = Order::updateOrCreate(
                ['order_number' => $definition['number']],
                [
                    'user_id' => $customer->id,
                    'checkout_token' => 'demo-'.$definition['number'],
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'status' => $definition['status'],
                    'payment_status' => $definition['payment_status'],
                    'currency' => 'BDT',
                    'subtotal_minor' => $price,
                    'shipping_minor' => 0,
                    'discount_minor' => 0,
                    'tax_minor' => 0,
                    'total_minor' => $price,
                    'delivery_method' => 'standard',
                    'payment_method' => 'cod',
                    'placed_at' => now()->subDays($definition['status'] === 'pending' ? 1 : 7),
                ]
            );

            $item = $order->items()->updateOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'unit_price_minor' => $price,
                    'quantity' => 1,
                    'line_total_minor' => $price,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'variant' => $variant->name,
                        'sku' => $variant->sku,
                        'price_minor' => $price,
                    ],
                ]
            );

            OrderAddress::updateOrCreate(
                ['order_id' => $order->id, 'type' => 'shipping'],
                [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address_line' => 'House 18, Road 7, Dhanmondi',
                    'city' => 'Dhaka',
                    'district' => 'Dhaka',
                    'postal_code' => '1205',
                    'country' => 'BD',
                ]
            );

            $order->statusHistory()->delete();
            $order->statusHistory()->create([
                'to_status' => 'pending',
                'note' => 'Demo order seeded.',
                'created_at' => now()->subDays(7),
            ]);

            if ($definition['status'] !== 'pending') {
                $order->statusHistory()->create([
                    'from_status' => 'pending',
                    'to_status' => $definition['status'],
                    'note' => 'Demo status seeded.',
                    'created_at' => now()->subDays(2),
                ]);
            }

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'provider' => 'cod',
                    'method' => 'cod',
                    'status' => $definition['payment_status'] === 'paid' ? 'paid' : 'pending',
                    'amount_minor' => $price,
                    'currency' => 'BDT',
                    'metadata' => ['demo' => true],
                    'paid_at' => $definition['payment_status'] === 'paid' ? now()->subDays(2) : null,
                ]
            );

            if ($definition['status'] === 'completed') {
                $verifiedItems[$product->id][$customer->id] = $item->id;
            }
        }

        return $verifiedItems;
    }

    private function seedProductReviews(Collection $customers, array $verifiedItems): void
    {
        $products = Product::query()->published()->orderBy('id')->get();
        $customerIds = $customers->pluck('id');

        foreach ($products as $productIndex => $product) {
            ProductReview::query()
                ->where('product_id', $product->id)
                ->whereIn('user_id', $customerIds)
                ->delete();

            // Deterministic 4, 5 or 6 reviews for every product.
            $reviewCount = 4 + (hexdec(substr(md5($product->slug), 0, 2)) % 3);
            $reviews = $this->buildReviews($product, $this->reviewProfile($product), $reviewCount, $productIndex);

            foreach ($reviews as $reviewIndex => $reviewData) {
                $customer = $customers[($productIndex + $reviewIndex * 3) % $customers->count()];
                $orderItemId = $verifiedItems[$product->id][$customer->id] ?? null;

                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $customer->id,
                    'order_item_id' => $orderItemId,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'rating' => $reviewData['rating'],
                    'title' => $reviewData['title'],
                    'review' => $reviewData['review'],
                    'status' => 'approved',
                    'is_verified_purchase' => $orderItemId !== null,
                    'approved_at' => now()->subDays(3 + (($productIndex * 11 + $reviewIndex * 7) % 150)),
                ]);
            }
        }
    }

    private function buildReviews(Product $product, array $profile, int $count, int $seed): array
    {
        $ratingPatterns = [
            [5, 4, 5, 4, 3, 5],
            [5, 5, 4, 4, 5, 3],
            [4, 5, 5, 4, 3, 4],
            [5, 4, 4, 5, 4, 3],
        ];
        $ratings = $ratingPatterns[$seed % count($ratingPatterns)];
        $titles = [
            'Happy with it so far',
            'Good for everyday use',
            'Solid value overall',
            'Does what I needed',
            'Mostly good, one small issue',
            'Would buy again',
            'Better than I expected',
            'Useful purchase',
        ];

        $templates = [
            "I bought {$product->name} mainly for %s. %s. I have been using it regularly and so far it has been dependable. %s, but it has not been a deal-breaker for me.",
            'I have mostly used this for %s. %s. The item arrived in good condition and matched what I expected from the listing. %s.',
            "After a few weeks with {$product->name}, the thing I notice most is that %s. It works well for %s. %s, although overall I am satisfied with it.",
            'I wanted something dependable for %s and this has fit that need well. %s. My only real complaint is that %s. For the price I am still happy.',
            'Overall my experience has been positive. %s. I have mainly been using it for %s. %s. Everything else has been pretty much as expected.',
            'I have had enough time to use it properly now. %s. It suits %s well. %s, but the overall quality and value still make sense to me.',
        ];

        $reviews = [];
        for ($i = 0; $i < $count; $i++) {
            $highlight = $profile['highlights'][($seed + $i) % count($profile['highlights'])];
            $useCase = $profile['use_cases'][($seed + $i * 2) % count($profile['use_cases'])];
            $caveat = $profile['caveats'][($seed + $i) % count($profile['caveats'])];
            $templateIndex = ($seed + $i) % count($templates);

            if ($templateIndex === 0 || $templateIndex === 1 || $templateIndex === 3) {
                $body = sprintf($templates[$templateIndex], $useCase, ucfirst($highlight), ucfirst($caveat));
            } else {
                $body = sprintf($templates[$templateIndex], $highlight, $useCase, ucfirst($caveat));
            }

            $reviews[] = [
                'rating' => $ratings[$i],
                'title' => $titles[($seed + $i * 2) % count($titles)],
                'review' => $body,
            ];
        }

        return $reviews;
    }

    private function reviewProfile(Product $product): array
    {
        $slug = $product->slug;

        return match (true) {
            str_contains($slug, 'wh-ch720n'),
            str_contains($slug, 'wf-c700n'),
            str_contains($slug, 'galaxy-buds'),
            str_contains($slug, 'redmi-buds') => $this->profile(
                ['the fit stays comfortable during longer listening sessions', 'background noise is reduced enough to make commuting more pleasant', 'music and spoken audio both sound clear', 'Bluetooth pairing has been stable', 'battery life has been good for daily listening'],
                ['the case picks up small marks fairly easily', 'the controls took me a little time to get used to', 'call quality drops a bit on very noisy streets'],
                ['daily commuting', 'music at work', 'podcasts and calls']
            ),

            str_contains($slug, 'wh-ch520') => $this->profile(
                ['battery life is excellent', 'the headphones feel very light', 'voices during calls come through clearly', 'Bluetooth connection has been stable', 'sound is clean for casual listening'],
                ['the earcups feel small after a very long session', 'there is no active noise cancellation', 'the plastic finish feels fairly basic'],
                ['online classes', 'work calls', 'music while studying']
            ),

            str_contains($slug, 'srs-xb100') => $this->profile(
                ['it sounds surprisingly full for such a small speaker', 'the carrying strap is genuinely useful', 'Bluetooth connects quickly', 'vocals stay clear at normal volume', 'battery life has been good on day trips'],
                ['deep bass is naturally limited by the small size', 'maximum volume can sound a little strained', 'the buttons are quite small'],
                ['a work desk', 'small gatherings', 'travel']
            ),

            str_contains($slug, 'note-13'),
            str_contains($slug, 'note-14'),
            str_contains($slug, 'galaxy-a15'),
            str_contains($slug, 'galaxy-a25') => $this->profile(
                ['the display is bright and pleasant to use', 'battery life comfortably gets through my normal day', 'regular apps feel responsive', 'daylight photos come out well', 'there is plenty of storage for normal use'],
                ['night photos are only average', 'the phone feels a bit large with a case', 'there are a few preinstalled apps I do not use'],
                ['social media and messaging', 'watching videos', 'daily calls and work apps']
            ),

            str_contains($slug, 'watch-5-active') => $this->profile(
                ['the large display is easy to read', 'battery lasts several days for me', 'basic workout tracking is convenient', 'notifications are easy to glance at', 'the watch feels light on the wrist'],
                ['health readings are better treated as general trends', 'the strap gets warm in humid weather', 'the software is simpler than a full smartwatch platform'],
                ['daily step tracking', 'checking notifications', 'light workouts']
            ),

            str_contains($slug, 'power-bank') => $this->profile(
                ['the large capacity is useful on long days outside', 'charging is quick with compatible devices', 'charging more than one device is convenient', 'the battery indicator is easy to understand', 'the body feels sturdy in a backpack'],
                ['it is noticeably heavier than a smaller power bank', 'a longer included cable would be useful', 'a full recharge of the power bank takes time'],
                ['travel', 'long workdays', 'charging my phone and earbuds together']
            ),

            str_contains($slug, 'wiz-') => $this->profile(
                ['setup in the app was straightforward', 'scheduled routines have worked reliably', 'remote control responds quickly', 'it reconnects automatically after power returns', 'the smart controls are genuinely convenient'],
                ['the app has more options than I really need', 'setup depends heavily on having stable Wi-Fi', 'the device is slightly bulkier than a basic alternative'],
                ['bedroom automation', 'evening routines', 'controlling things remotely']
            ),

            str_contains($slug, 'nivea-soft'),
            str_contains($slug, 'nivea-creme'),
            str_contains($slug, 'nivea-men-creme') => $this->profile(
                ['a small amount spreads surprisingly well', 'my skin stays comfortable for several hours', 'the texture feels good on dry areas', 'it has been reliable for regular moisturizing', 'the container lasts longer than I expected'],
                ['the fragrance is noticeable', 'I use less during very humid weather', 'the richer texture takes a little time to absorb'],
                ['dry hands', 'night-time moisturizing', 'after washing my face']
            ),

            str_contains($slug, 'face-wash') => $this->profile(
                ['it leaves my face feeling properly clean', 'a small amount is enough for one wash', 'it rinses off without much effort', 'the tube is convenient to use', 'it works well after a sweaty commute'],
                ['the fragrance is fairly noticeable', 'I would not use too much at once', 'it feels slightly drying in cooler weather'],
                ['morning face washing', 'after commuting', 'post-workout cleanup']
            ),

            str_contains($slug, 'kettle') => $this->profile(
                ['water boils quickly', 'automatic shutoff has worked reliably', 'the capacity is practical for several cups', 'the handle feels secure while pouring', 'the controls are simple'],
                ['the metal body gets hot', 'the power cable could be longer', 'water marks show on the steel finish'],
                ['morning tea', 'instant coffee', 'quick hot water']
            ),

            str_contains($slug, 'blender') => $this->profile(
                ['it handles shakes and chutney well', 'the controls are straightforward', 'the jar is easy to rinse', 'the motor has enough power for normal kitchen work', 'it does not take up too much counter space'],
                ['it is noisy on the highest setting', 'the lid needs to be seated carefully', 'I avoid running it continuously for too long'],
                ['smoothies', 'chutney and sauces', 'basic kitchen prep']
            ),

            str_contains($slug, 'rice-cooker') => $this->profile(
                ['rice cooks evenly', 'the keep-warm function is convenient', 'capacity is good for our household', 'the controls are very simple', 'it lets me prepare other dishes while the rice cooks'],
                ['the inner pot needs gentle utensils', 'condensation collects under the lid', 'the cooker takes up a fair amount of counter space'],
                ['family dinner', 'batch cooking rice', 'busy weekdays']
            ),

            str_contains($slug, 'cookware') => $this->profile(
                ['the different sizes cover most daily cooking', 'the handles feel secure', 'the pieces stack reasonably well', 'cleaning has been straightforward', 'the matching set looks tidy in the kitchen'],
                ['I avoid metal utensils on the cooking surface', 'the larger pieces need some cabinet space', 'the handles can get warm near a large flame'],
                ['family cooking', 'curries and vegetables', 'setting up a kitchen']
            ),

            str_contains($slug, 'taernaby') => $this->profile(
                ['the warm glow makes the room feel much cozier', 'the dimmer is smooth', 'the compact size works well beside the bed', 'the design has a lot of character', 'the build feels solid'],
                ['it is more ambient light than task lighting', 'the dark finish shows dust', 'bulb choice makes a big difference to the final look'],
                ['bedside lighting', 'a reading corner', 'evening ambient light']
            ),

            str_contains($slug, 'kallax'),
            str_contains($slug, 'lack-side-table') => $this->profile(
                ['assembly was straightforward', 'the simple design fits easily with other furniture', 'it is useful without taking over the room', 'the finish looks clean once assembled', 'it works well for the purpose I bought it for'],
                ['the surface can show scratches', 'it feels lighter than premium furniture', 'assembly is easier if the pieces are lined up carefully'],
                ['a small bedroom', 'living-room storage', 'organizing everyday items']
            ),

            str_contains($slug, 'apex-') => $this->profile(
                ['the fit matched my normal Apex size', 'the sole feels secure while walking', 'it has been comfortable for regular use', 'the design is easy to match with everyday clothes', 'the finishing is good for the price'],
                ['I would like a little more cushioning', 'it needed a short break-in period', 'the upper needs regular cleaning'],
                ['daily commuting', 'family visits', 'casual everyday wear']
            ),

            str_contains($slug, 'rice'),
            str_contains($slug, 'atta'),
            str_contains($slug, 'sugar') => $this->profile(
                ['the packet arrived properly sealed and dry', 'quality has been consistent', 'the texture and appearance were clean', 'the pack size is practical for our household', 'it performed exactly as expected in normal cooking'],
                ['a resealable package would be more convenient', 'the price changes slightly from time to time', 'I transfer it to an airtight container after opening'],
                ['regular home cooking', 'family meals', 'weekly pantry restocking']
            ),

            str_contains($slug, 'oil') => $this->profile(
                ['the bottle arrived properly sealed', 'the flavor and aroma were as expected', 'it has been consistent for normal cooking', 'the bottle size is convenient to store', 'a small amount works well in everyday dishes'],
                ['the cap area needs wiping after pouring', 'I would prefer a cleaner pour spout', 'the bottle can feel slippery during cooking'],
                ['family meals', 'traditional cooking', 'everyday kitchen use']
            ),

            str_contains($slug, 'noodles'),
            str_contains($slug, 'chanachur'),
            str_contains($slug, 'frooto') => $this->profile(
                ['the pack arrived fresh and intact', 'the flavor is familiar and enjoyable', 'it is convenient to keep at home', 'the portion size works well for us', 'the expiry date had plenty of time remaining'],
                ['I would prefer a resealable package', 'the flavor is a little strong if I have too much', 'I try not to finish it all at once'],
                ['evening snacks', 'serving guests', 'quick food at home']
            ),

            default => $this->profile(
                ['the product matched the listing', 'overall quality has been good for the price', 'it arrived in good condition', 'it has been dependable in normal use', 'the packaging was neat and secure'],
                ['there are a few small details that could be improved', 'I would still compare prices before ordering again', 'it took a little time to get used to'],
                ['everyday use', 'regular household use', 'my normal routine']
            ),
        };
    }

    private function profile(array $highlights, array $caveats, array $useCases): array
    {
        return [
            'highlights' => $highlights,
            'caveats' => $caveats,
            'use_cases' => $useCases,
        ];
    }
}
