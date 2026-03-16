# Avtotest Web Ilovasi 🚗 

Bu loyiha Avtotest qoidalarini o'rganish va testlarni yechish uchun mo'ljallangan veb-tizim hisoblanadi. Tizim orqali o'quvchilar turli variantlar (biletlar) bo'yicha onlayn test topshirishlari va o'z bilimlarini sinab ko'rishlari mumkin.

## 🌟 Asosiy imkoniyatlar (Features)

- **Foydalanuvchilar qismi (O'quvchilar uchun):**
  - Oson va qulay ro'yxatdan o'tish hamda tizimga kirish (`register.php`, `login.php`).
  - Mavjud biletlar va test variantlaridan xohlaganini tanlash imkoniyati (`dashboard.php`).
  - Testni ishlash jarayoni (har bir bilet odatda 20 ta savoldan iborat) va rasmli savollarni ko'rish imkoniyati (`test.php`, `submit_test.php`).

- **Admin panel (Boshqaruv qismi):**
  - Tizimni boshqarish va foydalanuvchilar holatini kuzatish (`admin.php`).
  - **Ommaviy savol yuklash (Bulk upload):**
    - Yangi test savollarini TXT formatida tizimga oson yuklash imkoniyati.
    - ZIP fayllar orqali rasmlari bilan birgalikda ommaviy yuklash (*rasmlarni to'g'ri savollar bilan moslashtirish logikasi kiritilgan*).
  
## 🛠 Ishlatilgan texnologiyalar (Tech Stack)

- **Backend:** PHP
- **Ma'lumotlar bazasi:** MySQL
- **Frontend:** HTML, CSS (Asosiy dizayn - `style.css`), JavaScript
- **Server:** XAMPP (Apache, MySQL)

## ⚙️ O'rnatish va ishga tushirish (Installation)

1. **Repozitoriyni klonlash:**
   ```bash
   git clone https://github.com/Abdurashid001/avtotest_web.git
   cd avtotest_web
   ```

2. **Serverni sozlash:**
   Ushbu loyihani `XAMPP`, `WAMP` yoki `MAMP` kabi lokal serverning ommaviy papkasiga (masalan: `htdocs` yoki `www`) joylashtiring.

3. **Maʼlumotlar bazasini sozlash:**
   - MySQL (phpMyAdmin orqali) da yangi baza yarating.
   - Baza nomini va ulanish ma'lumotlarini `db.php` faylida to'g'rilab chiqing (bazaning o'zi avtomatik tarzda sozlanishi uchun quyidagi fayllardan foydalaniladi).
   - Jadvallarni yaratish (migratsiya) va boshlang'ich ma'lumotlarni qo'shish uchun mos ravishda ishga tushiring:
     - `http://localhost/avtotest_web/migrate.php` - Jadvallarni yaratadi.
     - `http://localhost/avtotest_web/populate_db.php` - Boshlang'ich testlarni (agar mavjud bo'lsa) bazaga kiritadi.

4. **Tizimga kirish:**
   - Asosiy sahifani ochib ishlatib ko'rishingiz mumkin: `http://localhost/avtotest_web/`

## 👨‍💻 Muallif

- [Abdurashid](https://github.com/Abdurashid001)

---
*Loyiha davomida xatolik topsangiz yoki yangi imkoniyatlar qo'shmoqchi bo'lsangiz bemalol PR (Pull Request) qoldirishingiz mumkin.*
