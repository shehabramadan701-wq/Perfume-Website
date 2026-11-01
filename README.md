# AURA — موقع متجر برفانات (Laravel)

موقع متجر عطور فاخر بتصميم داكن، فيه **مقدمة متحركة لزجاجة البرفان وهي نازلة من السما**، ومعاه **لوحة تحكم (Dashboard)** للمنتجات والطلبات والعملاء والحسابات والرسائل، وبوابات دفع **Paymob** و **Stripe** والدفع عند الاستلام.

المشروع معمول بطريقة بسيطة عشان تعرف تعدل عليه بسهولة:
- مفيش Vite ولا npm build — ملفات CSS و JS عادية في `public/css` و `public/js`.
- الصور اللي بترفعها من الداشبورد بتتحفظ في `public/uploads/products`.
- كل إعدادات المحل (الاسم، العملة، الشحن، التليفون، الواتساب) في ملف `.env`.

---

## التشغيل من GitHub مباشرة (Codespaces) — من غير ما تسطب حاجة

1. افتح صفحة الريبو على GitHub.
2. اضغط الزرار الأخضر **Code** ← تبويب **Codespaces** ← **Create codespace on ...**
3. استنى 2-3 دقايق أول مرة (بيسطب كل حاجة لوحده من ملف `.devcontainer`).
4. الموقع بيشتغل لوحده، وهيظهرلك لينك في تبويب **PORTS** (port 8000) — افتحه.
   - الداشبورد: نفس اللينك + `/admin`
   - عشان تبعت اللينك لحد تاني: في تبويب PORTS كليك يمين على 8000 ← **Port Visibility** ← **Public**.

> الـ Codespace بيقفل لوحده لو مفيش استخدام (حوالي 30 دقيقة) واللينك بيبطل يفتح — ده للتجربة والتطوير مش سيرفر دائم.
> لما تفتحه تاني من نفس المكان بيرجع يشتغل والبيانات زي ما هي. الحساب المجاني فيه حوالي 60 ساعة تشغيل في الشهر.

## التشغيل لأول مرة على جهازك

المطلوب: PHP 8.2 أو أحدث (XAMPP 8.2 شغال)، و Composer، و MySQL.

**قاعدة البيانات MySQL** — لو بتستخدم XAMPP:
1. افتح XAMPP Control Panel ودوس **Start** جنب **MySQL**.
2. افتح http://localhost/phpmyadmin واعمل قاعدة بيانات جديدة اسمها `aura_perfume` واختار `utf8mb4_unicode_ci`.
3. الإعدادات الجاهزة في `.env` هي بتاعة XAMPP (`root` من غير باسورد). لو عندك باسورد حطه في `DB_PASSWORD`.

وبعدين في الـ terminal جوه فولدر المشروع:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

(على ماك أو لينكس استخدم `cp` بدل `copy`.)

افتح الموقع: http://localhost:8000
ولوحة التحكم: http://localhost:8000/admin

| | |
|---|---|
| الإيميل | `admin@aura.test` |
| الباسورد | `password` |

> ⚠️ غير الباسورد قبل ما ترفع الموقع (مثلاً من `php artisan tinker`).
> الـ seeder بيضيف منتجات وطلبات وعملاء تجريبيين عشان الداشبورد يبان مليان — تقدر تمسحهم من الداشبورد أو تشغل `php artisan migrate:fresh` من غير `--seed`.

إعدادات القاعدة في `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aura_perfume
DB_USERNAME=root
DB_PASSWORD=
```

---

## مقدمة الموقع (البرفان النازل من السما)

- أول ما حد يفتح الصفحة الرئيسية بتظهر نجوم، وبعدين الزجاجة بتنزل من فوق بذيل ضوء، وتخبط في الأرض بموجة ضوء وأوراق شجر طايرة، وبعدين يظهر اسم المحل.
- بتظهر مرة واحدة بس في كل زيارة، وفيه زرار **SKIP**، وزرار **Play Atmosphere** في الصفحة الرئيسية بيعيدها.
- **عايز فيديو حقيقي بدل الأنيميشن؟** حط الفيديو في `public/videos/intro.mp4` وهيشتغل لوحده مكانها.
- الكود في `public/js/shop.js` (جزء Intro) و `public/css/shop.css` (جزء Intro).

---

## لوحة التحكم `/admin`

| الصفحة | فيها إيه |
|---|---|
| **Dashboard** | إجمالي الإيرادات، المدفوع بالفيزا، عدد الطلبات والعملاء، رسم بياني لإيرادات آخر 14 يوم، توزيع طرق الدفع، أحدث الطلبات، الأكثر مبيعاً |
| **Orders** | كل الطلبات مع بحث وفلتر (حالة الطلب / حالة الدفع / طريقة الدفع)، صفحة لكل طلب فيها بيانات العميل والكارت (نوعه وآخر 4 أرقام) ورقم العملية، وتغيير الحالة |
| **Products** | إضافة وتعديل وحذف برفانات، رفع صورة، السعر والسعر القديم (للخصم)، المخزون، إظهار/إخفاء، تمييز في الصفحة الرئيسية |
| **Customers** | كل العملاء وعدد طلباتهم وإجمالي اللي دفعوه، صفحة لكل عميل فيها طلباته وملاحظات خاصة وزرار واتساب |
| **Accounting** | الحسابات: إجمالي الدخل، المدفوع بالكارت، الكاش المحصّل، الكاش اللي لسه هيتحصّل، المصروفات، صافي الربح، كل العمليات المدفوعة، فلتر بالتاريخ، **تصدير CSV للإكسيل** |
| **Messages** | رسائل صفحة Contact us |

---

## بوابات الدفع

### Paymob (مصر)
1. اعمل حساب على https://accept.paymob.com
2. من **Settings / Developers** هات: `Secret Key` و `Public Key` و `HMAC`، ومن **Payment Integrations** هات `Integration ID` بتاع الكارت.
3. حطهم في `.env`:
   ```
   PAYMOB_SECRET_KEY=...
   PAYMOB_PUBLIC_KEY=...
   PAYMOB_INTEGRATION_ID=...
   PAYMOB_HMAC=...
   ```
4. في إعدادات الـ Integration على Paymob حط:
   - **Transaction processed callback:** `https://your-domain.com/webhooks/paymob`
   - **Transaction response callback:** `https://your-domain.com/payment/paymob/callback`

### Stripe
1. من https://dashboard.stripe.com/apikeys هات المفاتيح وحطها في `STRIPE_KEY` و `STRIPE_SECRET`.
2. (اختياري بس مفضل) من **Developers → Webhooks** اعمل endpoint على
   `https://your-domain.com/webhooks/stripe` بالحدث `checkout.session.completed` وحط الـ signing secret في `STRIPE_WEBHOOK_SECRET`.

> طريقة الدفع اللي مفاتيحها مش موجودة بتظهر في صفحة الدفع على إنها "not configured yet"، والدفع عند الاستلام شغال دايماً.
> بيانات الكارت نفسها بيدخلها العميل في صفحة Paymob/Stripe الآمنة — الموقع مش بيخزن غير نوع الكارت وآخر 4 أرقام ورقم العملية.

---

## تعديل الشكل والمحتوى

| عايز تعدل | الملف |
|---|---|
| الألوان والخطوط | أول الملف `public/css/shop.css` (المتغيرات في `:root`) |
| ألوان الداشبورد | `public/css/admin.css` |
| نصوص الصفحة الرئيسية | `resources/views/shop/home.blade.php` |
| الهيدر والفوتر | `resources/views/layouts/shop.blade.php` |
| صفحة Our Story | `resources/views/shop/story.blade.php` |
| صورة الـ Hero | `public/images/hero-bottle.webp` |
| اسم المحل، العملة، الشحن، التليفون | `.env` (متغيرات `SHOP_...`) |

## ملفات الكود المهمة

```
app/Http/Controllers/          # صفحات الموقع (Home, Shop, Cart, Checkout, Payment, Contact)
app/Http/Controllers/Admin/    # صفحات الداشبورد
app/Services/PaymobPayment.php # كل كود Paymob
app/Services/StripePayment.php # كل كود Stripe
app/Support/Cart.php           # سلة المشتريات
app/Models/                    # Product, Order, Customer, Expense, ContactMessage
routes/web.php                 # كل الروابط
```

## الاختبارات

```bash
php artisan test
```
