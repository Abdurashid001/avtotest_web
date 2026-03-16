<?php
require 'db.php';

$questions = [
    [
        "Svetoforning qizil chirog'ida harakatlanish:", 
        "Taqiqlanadi", "Ruxsat etiladi", "Faqat o'ngga ruxsat etiladi", "Faqat tunda ruxsat etiladi", "a",
        "Svetoforning qizil chirog'i (shu jumladan miltillovchi qizil chiroq ham) qat'iyan harakatlanishni taqiqlaydi."
    ],
    [
        "Piyodalar o'tish joyida kimga afzallik beriladi?", 
        "Avtomobillarga", "Piyodalarga", "Velosipedchilarga", "Mototsikllarga", "b",
        "Piyodalar o'tish joyida har doim piyodalarga yo'l berish majburiydir."
    ],
    [
        "Aholi punktlarida avtomobillarning eng yuqori tezligi odatda qancha bo'lishi kerak?", 
        "60 km/soat", "70 km/soat", "50 km/soat", "90 km/soat", "a",
        "O'zbekiston Respublikasi Yo'l harakati qoidalariga asosan, aholi punktlarida ruxsat etilgan eng yuqori tezlik soatiga 60 km etib belgilangan."
    ],
    [
        "Xavfsizlik kamarini taqish kimlar uchun majburiy?", 
        "Faqat haydovchi uchun", "Barcha yo'lovchilar va haydovchi uchun", "Faqat oldingi o'rindiqdagi yo'lovchilar uchun", "Majburiy emas", "b",
        "Xavfsizlik kamari bilan jihozlangan transport vositasida barcha yo'lovchilar, shu jumladan orqa o'rindiqdagi yo'lovchilar ham xavfsizlik kamarini taqishlari shart."
    ],
    [
        "Tartibga solinmagan teng huquqli chorrahada qaysi transport vositasiga afzallik beriladi?", 
        "Chapdan kelayotgan transport vositasiga", "O'ngdan kelayotgan transport vositasiga", "To'g'riga harakatlanayotgan transport vositasiga", "Katta o'lchamli mashinalarga", "b",
        "Teng huquqli yo'llar kesishgan chorrahada haydovchi o'ng tomondan yaqinlashib kelayotgan transport vositasiga yo'l berishi shart (O'ng qo'l qoidasi)."
    ],
    [
        "Avtomagistralda orqaga harakatlanish ruxsat etiladimi?", 
        "Ha, faqat o'ng chekkada", "Yo'q, qat'iyan taqiqlanadi", "Ha, agar boshqa avtomobillar bo'lmasa", "Maxsus jihozlangan bo'lsa", "b",
        "Avtomagistralda har qanday holatda orqaga harakatlanish, qayrilib olish va to'xtab turish qat'iyan taqiqlanadi."
    ],
    [
        "Sutkaning qorong'i vaqtida qanday chiroqlar yoqilishi shart?", 
        "Faqat gabarit chiroqlari", "Yaqinni yoki uzoqni yorituvchi chiroqlar", "Tumanga qarshi chiroqlar", "Ichki salon chiroqlari", "b",
        "Sutkaning qorong'i vaqtida va yetarlicha ko'rinmaydigan sharoitda, shuningdek, tunnellarda transport vositalarining uzoqni yoki yaqinni yorituvchi faralari yoqilishi kerak."
    ],
    [
        "Yo'l belgisi \"To'xtash taqiqlanadi\" qanday shaklda bo'ladi?", 
        "Uchburchak", "Doira", "To'rtburchak", "Romb", "b",
        "Taqiqlovchi belgilar asosan qizil rangli doira shaklida bo'ladi."
    ],
    [
        "Transport vositasini mast holda boshqarish:", 
        "Jarima va guvohnomadan mahrum qilishga olib keladi", "Faqat ogohlantirish beriladi", "Ruxsat etiladi, agar tezlik past bo'lsa", "Faqat jarimaga sabab bo'ladi", "a",
        "Mast holda transport boshqarish og'ir qoidabuzarlik hisoblanib, katta miqdorda jarima solinadi va transport boshqarish huquqidan mahrum etiladi."
    ],
    [
        "Qaysi holatda quvib o'tish taqiqlanadi?", 
        "Keng yo'llarda", "Chorrahalarda", "Bir tomonlama harakatlanish yo'lida", "Ikki yo'lakli yo'llarda", "b",
        "Asosiy yo'lda bo'lmagan chorrahalarda, piyodalar o'tish joylarida, temir yo'l kesishmalarida va ko'rinish cheklangan joylarda quvib o'tish taqiqlanadi."
    ],
    [
        "Tovushli ishoralardan qachon foydalanish ruxsat etiladi?", 
        "Boshqa haydovchilar bilan salomlashish uchun", "Faqat yo'l-transport hodisasini oldini olish uchun", "Aholi punktlarida xohlagan vaqtda", "Ob-havo yomonlashganda", "b",
        "Tovushli ishoralar faqatgina yo'l-transport hodisasining oldini olish zaruriyati tug'ilganda chalinishi mumkin."
    ],
    [
        "Svetoforning miltillovchi sariq chirog'i nimani anglatadi?", 
        "Harakatlanish taqiqlanganligini", "Chorraha tartibga solinmaganligini va diqqatni jamlashni", "Tezlikni oshirish kerakligini", "Piyodalar o'tayotganligini", "b",
        "Miltillovchi sariq chiroq ruxsat etilgan harakatni bildiradi va tartibga solinmagan chorraha (yoki piyodalar o'tish joyi) mavjudligini, xavf haqida ogohlantirishni bildiradi."
    ],
    [
        "Qanday ob-havo sharoitida tumanga qarshi chiroqlardan foydalanish ruxsat etiladi?", 
        "Kuchli yomg'ir, qor yoki tumanda", "Ochiq va quyoshli havoda", "Faqat kunduzi", "Issiq havo sharoitida", "a",
        "Tumanga qarshi faralar yetarli ko'rinmaydigan sharoitda (tuman, qor, yomg'ir) qo'llanilishi lozim."
    ],
    [
        "Bolalarni old o'rindiqda maxsus o'rindig'siz tashish mumkinmi (12 yoshgacha)?", 
        "Yo'q, qat'iyan taqiqlanadi", "Ha, kattalar quchog'ida", "Ha, past tezlikda", "Ha, qisqa masofaga", "a",
        "12 yoshga to'lmagan bolalarni old o'rindiqda faqat maxsus bolalar o'rindig'ida olib yurishga ruxsat etiladi."
    ],
    [
        "Asosiy yo'l belgisi qanday shaklga ega?", 
        "Sariq romb", "Oq uchburchak", "Qizil doira", "Ko'k to'rtburchak", "a",
        "2.1 \"Asosiy yo'l\" belgisi sariq rangli romb shaklida bo'ladi."
    ],
    [
        "Tibbiy yordam qutichasi (aptechka) mashinada bo'lishi shartmi?", 
        "Ha, har doim bo'lishi shart", "Yo'q, ixtiyoriy", "Faqat uzoq safarlarda", "Faqat yuk mashinalarida", "a",
        "Yo'l harakati xavfsizligini ta'minlash maqsadida har bir transport vositasida tibbiy yordam qutichasi (aptechka), o't o'chirgich kabi jihozlar bo'lishi shart."
    ],
    [
        "Qaysi holatlarda avtomobilni egiluvchan tirkamada shatakka olish taqiqlanadi?", 
        "Tormoz tizimi ishlamayotgan bo'lsa", "Yoqilg'i tugab qolganda", "Akkumulyator o'tirib qolganda", "Shinalar yorilganda", "a",
        "Ishchi tormoz tizimi nosoz bo'lgan transport vositalarini egiluvchan sirtmoq (tros) bilan shatakka olish harakatlanish xavfsizligi qoidalariga ko'ra taqiqlanadi."
    ],
    [
        "Yo'nalish ko'rsatkichini (burilish chirog'ini) qachon yoqish kerak?", 
        "Manyovrni boshlashdan oldin", "Manyovr tugagandan keyin", "Chorrahani o'rtasida", "Faqat tunda", "a",
        "Haydovchi manyovr (burilish, qayrilib olish jadvalga chiqish) bajarishni boshlashdan oldin tegishli yo'nalish ko'rsatkichlarini yoqishi shart."
    ],
    [
        "Yo'lovchilarni tashish qoidalari qaysi javobda to'g'ri ko'rsatilgan?", 
        "O'rindiqlar sonidan ko'p yo'lovchi tashish mumkin", "Yukxona (bagaj) qismida yo'lovchi tashish taqiqlanadi", "Barcha javoblar to'g'ri", "Faqat shahar ichida taqiqlanadi", "b",
        "Odamlarni yukxonada, tirkamalarda yoxud transport vasitasi o'rindiqlari sonidan ortiqcha miqdorda tashish qat'iyan taqiqlanadi."
    ],
    [
        "\"Kirish taqiqlanadi\" (G'isht) belgisi kimlarga ta'sir qilmaydi?", 
        "Hech kimga ta'sir qilmaydi", "Barcha transport vositalariga", "Yo'nalishli transport vositalariga (avtobus, marshrutka)", "Oq rangli avtomobillarga", "c",
        "3.1 \"Kirish taqiqlanadi\" belgisi yo'nalishli transport vositalaridan (avtobus, tramvay, mikroavtobuslar) tashqari barcha transport vositalari kirishini taqiqlaydi."
    ]
];

$stmt = $db->prepare('INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?)');

$count = 0;
foreach ($questions as $q) {
    if ($stmt->execute($q)) {
        $count++;
    }
}

echo "Muvaffaqiyatli $count ta izohli savol qo'shildi!\n";
?>
