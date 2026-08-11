<?php
/**
 * Translation builder for the CosyPaw theme.
 *
 * Generates languages/{en_US,ru_RU,sr_RS}.{po,mo} from the maps below. Source
 * (msgid) strings are mostly Serbian (front-end) with some English (admin).
 *
 *   - Default site (sr): Serbian msgids fall through untranslated; only the
 *     English-source admin strings get an sr_RS translation.
 *   - en_US: Serbian msgids -> English (English msgids fall through unchanged).
 *   - ru_RU: everything -> Russian.
 *
 * Run with any PHP 8.x:  php tools/build-translations.php
 * No gettext/msgfmt needed — .mo is packed directly.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

// Serbian-source msgid => [ english, russian ].
$sr_source = array(
	'CosyPaw žurnal'                                                                          => array( 'CosyPaw journal', 'Журнал CosyPaw' ),
	'Prethodna'                                                                               => array( 'Previous', 'Предыдущая' ),
	'Sledeća'                                                                                 => array( 'Next', 'Следующая' ),
	'Ova stranica se sakrila'                                                                 => array( 'This page is hiding', 'Эта страница спряталась' ),
	'Stranicu nismo našli — možda je premeštena. Probaj pretragu ili se vrati na početnu.'    => array( "We couldn't find the page — it may have moved. Try a search or head back home.", 'Мы не нашли страницу — возможно, она перемещена. Попробуйте поиск или вернитесь на главную.' ),
	'Nazad na početnu'                                                                        => array( 'Back to home', 'На главную' ),
	'Pretraga'                                                                                => array( 'Search', 'Поиск' ),
	'Pretraži…'                                                                               => array( 'Search…', 'Поиск…' ),
	'Traži'                                                                                   => array( 'Search', 'Найти' ),
	'Ručno šiveni peškirići-ljubimci koji čine kupatilo mekanim, urednim i — preslatkim.'     => array( 'Hand-sewn towel pets that make your bathroom soft, tidy and — adorable.', 'Сшитые вручную полотенца-зверушки, которые делают ванную мягкой, аккуратной и — очаровательной.' ),
	'Mekani svet peškiriča'                                                                   => array( 'A soft world of little towels', 'Мягкий мир полотенчиков' ),
	'Slatki peškirići koji grle %s'                                                           => array( 'Cute towels that hug %s', 'Милые полотенца, что обнимают %s' ),
	'tvoje kupatilo'                                                                          => array( 'your bathroom', 'твою ванную' ),
	'Izaberi paket'                                                                           => array( 'Choose a package', 'Выбери набор' ),
	'Pogledaj motive'                                                                         => array( 'See the designs', 'Смотреть мотивы' ),
	'Ručni rad'                                                                               => array( 'Handmade', 'Ручная работа' ),
	'Mekano i upijajuće'                                                                      => array( 'Soft & absorbent', 'Мягко и впитывает' ),
	'Besplatna dostava na 3'                                                                  => array( 'Free shipping on 3', 'Бесплатная доставка от 3' ),
	'Izdvojeni motivi'                                                                        => array( 'Featured designs', 'Избранные мотивы' ),
	'od %s'                                                                                   => array( 'from %s', 'от %s' ),
	'Zašto CosyPaw'                                                                           => array( 'Why CosyPaw', 'Почему CosyPaw' ),
	'Mali zagrljaj pored sudopere'                                                            => array( 'A little hug by the sink', 'Маленькое объятие у раковины' ),
	'Svaki peškirić je mekan, upijajuć i ima alku za kačenje — uvek pri ruci, uvek sladak.'   => array( 'Every towel is soft, absorbent and has a hanging loop — always at hand, always cute.', 'Каждое полотенце мягкое, впитывающее и с петелькой — всегда под рукой, всегда милое.' ),
	'Mekano kao oblak'                                                                        => array( 'Soft as a cloud', 'Мягкое, как облако' ),
	'Plišana mikrofibra prijatna i nežnoj dečjoj koži.'                                       => array( "Plush microfiber, gentle even on a child's skin.", 'Плюшевая микрофибра, приятная даже нежной детской коже.' ),
	'Upija u trenu'                                                                           => array( 'Absorbs instantly', 'Впитывает мгновенно' ),
	'Brzo suši ručice i ostaje suv i svež tokom dana.'                                        => array( 'Dries little hands fast and stays dry and fresh all day.', 'Быстро сушит ручки и остаётся сухим и свежим весь день.' ),
	'Alka za kačenje'                                                                         => array( 'Hanging loop', 'Петелька для подвешивания' ),
	'Okačiš ga na kuku ili ručku — uvek na svom mestu.'                                       => array( 'Hang it on a hook or handle — always in its place.', 'Повесь на крючок или ручку — всегда на своём месте.' ),
	'Savršen poklon'                                                                          => array( 'Perfect gift', 'Идеальный подарок' ),
	'Slatka sitnica koja uvek izmami osmeh i „awww”.'                                         => array( 'A sweet little thing that always brings a smile and an “awww”.', 'Милая мелочь, что всегда вызывает улыбку и «ня».' ),
	'Što više grlića, veća ušteda'                                                            => array( 'The more cuties, the bigger the saving', 'Чем больше обнимашек, тем больше выгода' ),
	'Izmešaj motive kako želiš — cena po komadu pada sa svakim sledećim.'                     => array( 'Mix the designs however you like — the price per piece drops with each one.', 'Сочетай мотивы как хочешь — цена за штуку падает с каждым следующим.' ),
	'kom'                                                                                     => array( 'pc', 'шт' ),
	'Besplatna dostava'                                                                       => array( 'Free shipping', 'Бесплатная доставка' ),
	'Dodaj u korpu'                                                                           => array( 'Add to cart', 'В корзину' ),
	'Plaćanje pouzećem • Dostava 2–4 radna dana'                                              => array( 'Cash on delivery • Delivery in 2–4 business days', 'Оплата при получении • Доставка 2–4 рабочих дня' ),
	'Cela družina'                                                                            => array( 'The whole crew', 'Вся компания' ),
	'Upoznaj sve motive'                                                                      => array( 'Meet all the designs', 'Познакомься со всеми мотивами' ),
	'Od %s po komadu — ili ih spoji u paket i uštedi.'                                        => array( 'From %s each — or bundle them and save.', 'От %s за штуку — или собери в набор и сэкономь.' ),
	'%s • 1 kom'                                                                              => array( '%s • 1 pc', '%s • 1 шт' ),
	'Korpa'                                                                                   => array( 'Cart', 'Корзина' ),
	'Kupi'                                                                                    => array( 'Buy', 'Купить' ),
	'Dodato u korpu'                                                                          => array( 'Added to cart', 'Добавлено в корзину' ),
	'Dodato'                                                                                  => array( 'Added', 'Добавлено' ),
	'Rezultati za „%s”'                                                                       => array( 'Results for “%s”', 'Результаты по запросу «%s»' ),
	'Mekani svet peškiriča. Ručno šiveni ljubimci koji čine svako kupatilo toplijim.'         => array( 'A soft world of little towels. Hand-sewn pets that make every bathroom warmer.', 'Мягкий мир полотенчиков. Сшитые вручную зверушки, что делают любую ванную теплее.' ),
	'Brzi linkovi'                                                                            => array( 'Quick links', 'Быстрые ссылки' ),
	'Paketi'                                                                                  => array( 'Packages', 'Наборы' ),
	'Svi motivi'                                                                              => array( 'All designs', 'Все мотивы' ),
	'Poručivanje'                                                                             => array( 'Ordering', 'Заказ' ),
	'Poruči preko DM-a ili korpe.<br>Plaćanje pouzećem.'                                      => array( 'Order via DM or the cart.<br>Cash on delivery.', 'Заказывай через DM или корзину.<br>Оплата при получении.' ),
	'© %d CosyPaw — Mekani svet peškiriča'                                                    => array( '© %d CosyPaw — A soft world of little towels', '© %d CosyPaw — Мягкий мир полотенчиков' ),
	'Ručni rad • Made with love'                                                              => array( 'Handmade • Made with love', 'Ручная работа • Сделано с любовью' ),
	'Zatvori korpu'                                                                           => array( 'Close cart', 'Закрыть корзину' ),
	'Tvoja korpa'                                                                             => array( 'Your cart', 'Твоя корзина' ),
	'Zatvori'                                                                                 => array( 'Close', 'Закрыть' ),
	'Korpa je još prazna'                                                                     => array( 'Your cart is still empty', 'Корзина пока пуста' ),
	'Izaberi paket ili omiljeni motiv.'                                                       => array( 'Pick a package or a favorite design.', 'Выбери набор или любимый мотив.' ),
	'Ukupno'                                                                                  => array( 'Total', 'Итого' ),
	'Nastavi ka plaćanju'                                                                     => array( 'Proceed to checkout', 'Перейти к оплате' ),
	'Ručni rad sa puno ljubavi'                                                               => array( 'Handmade with lots of love', 'Ручная работа с большой любовью' ),
	'Besplatna dostava na Trio paket'                                                         => array( 'Free shipping on the Trio package', 'Бесплатная доставка для набора Trio' ),
	'Glavna navigacija'                                                                       => array( 'Main navigation', 'Основная навигация' ),
	'Pređi na sadržaj'                                                                        => array( 'Skip to content', 'Перейти к содержимому' ),
	'Meni'                                                                                    => array( 'Menu', 'Меню' ),
	'Pauziraj najave'                                                                         => array( 'Pause the announcements', 'Приостановить объявления' ),
	'Pusti najave'                                                                            => array( 'Play the announcements', 'Возобновить объявления' ),
	'Pauziraj smenjivanje motiva'                                                             => array( 'Pause the design slideshow', 'Приостановить смену мотивов' ),
	'Pusti smenjivanje motiva'                                                                => array( 'Play the design slideshow', 'Возобновить смену мотивов' ),
	'Motivi'                                                                                  => array( 'Designs', 'Мотивы' ),
	'Pogledaj korpu'                                                                          => array( 'View cart', 'Посмотреть корзину' ),
	'Otvori korpu'                                                                            => array( 'Open cart', 'Открыть корзину' ),
	'dodat u korpu'                                                                           => array( 'added to cart', 'добавлено в корзину' ),
	'Demo prodavnice — porudžbina nije aktivna'                                               => array( 'Demo shop — ordering is not active', 'Демо-магазин — заказ недоступен' ),
	'Ukloni'                                                                                  => array( 'Remove', 'Удалить' ),
	'RSD'                                                                                     => array( 'RSD', 'RSD' ),
	'Žirafa'                                                                                  => array( 'Giraffe', 'Жираф' ),
	'Koala'                                                                                   => array( 'Koala', 'Коала' ),
	'Pingvin'                                                                                 => array( 'Penguin', 'Пингвин' ),
	'Sova'                                                                                    => array( 'Owl', 'Сова' ),
	'Panda'                                                                                   => array( 'Panda', 'Панда' ),
	'Meda'                                                                                    => array( 'Teddy bear', 'Мишка' ),
	'Kapibara'                                                                                => array( 'Capybara', 'Капибара' ),
	'Maca'                                                                                    => array( 'Kitty', 'Кошечка' ),
	'Kucence'                                                                                 => array( 'Puppy', 'Щенок' ),
	'Zeka'                                                                                    => array( 'Bunny', 'Зайчик' ),
	'Avokado'                                                                                 => array( 'Avocado', 'Авокадо' ),
	'Ananas'                                                                                  => array( 'Pineapple', 'Ананас' ),
	'Trešnja'                                                                                 => array( 'Cherry', 'Вишня' ),
	'Sir'                                                                                     => array( 'Cheese', 'Сыр' ),
	'Krofna'                                                                                  => array( 'Donut', 'Пончик' ),
	'Biskvit'                                                                                 => array( 'Biscuit', 'Бисквит' ),
	'Čokoladni keks'                                                                          => array( 'Chocolate cookie', 'Шоколадное печенье' ),
	'Tost'                                                                                    => array( 'Toast', 'Тост' ),
	'Lala'                                                                                    => array( 'Tulip', 'Тюльпан' ),
	'Javorov list'                                                                            => array( 'Maple leaf', 'Кленовый лист' ),
	'Pojedinačno'                                                                             => array( 'Single', 'Поштучно' ),
	'Jedan omiljeni motiv'                                                                    => array( 'One favorite design', 'Один любимый мотив' ),
	'Duo paket'                                                                               => array( 'Duo package', 'Набор Duo' ),
	'Ušteda 380 RSD'                                                                          => array( 'Save 380 RSD', 'Экономия 380 RSD' ),
	'Dva motiva po izboru'                                                                    => array( 'Two designs of your choice', 'Два мотива на выбор' ),
	'Trio paket'                                                                              => array( 'Trio package', 'Набор Trio' ),
	'Najpopularnije'                                                                          => array( 'Most popular', 'Самое популярное' ),
	'Tri motiva po izboru'                                                                    => array( 'Three designs of your choice', 'Три мотива на выбор' ),
	'Žurnal'                                                                                  => array( 'Journal', 'Журнал' ),
	'Pročitaj više'                                                                           => array( 'Read more', 'Читать далее' ),
	'Strane:'                                                                                 => array( 'Pages:', 'Страницы:' ),
	'Ništa nije pronađeno'                                                                    => array( 'Nothing found', 'Ничего не найдено' ),
	'Nažalost, ništa ne odgovara pretrazi. Probaj druge ključne reči.'                        => array( 'Sorry, nothing matches your search. Try other keywords.', 'К сожалению, ничего не найдено. Попробуйте другие ключевые слова.' ),
	'Ovde još nema sadržaja.'                                                                 => array( "There's no content here yet.", 'Здесь пока нет содержимого.' ),
	'Napravi svoj paket'                                                                      => array( 'Build your package', 'Собери свой набор' ),
	'Izaberi veličinu paketa, pa ubaci omiljene motive. Cena po komadu pada sa svakim sledećim.' => array( 'Pick a package size, then drop in your favorite designs. The price per piece drops with each one.', 'Выбери размер набора, затем добавь любимые мотивы. Цена за штуку падает с каждым следующим.' ),
	'Izaberi veličinu paketa'                                                                 => array( 'Choose a package size', 'Выбери размер набора' ),
	'Ubaci svoje motive'                                                                      => array( 'Add your designs', 'Добавь свои мотивы' ),
	'Izabrano'                                                                                => array( 'Selected', 'Выбрано' ),
	'Iznenadi me'                                                                             => array( 'Surprise me', 'Удиви меня' ),
	'Očisti'                                                                                  => array( 'Clear', 'Очистить' ),
	'Motivi'                                                                                  => array( 'Designs', 'Мотивы' ),
	'Motiv %d'                                                                                => array( 'Design %d', 'Мотив %d' ),
	'Dodaj u korpu • %s'                                                                      => array( 'Add to cart • %s', 'В корзину • %s' ),
	'Izaberi još %d'                                                                          => array( 'Choose %d more', 'Выбери ещё %d' ),
	'motiv'                                                                                   => array( 'design', 'мотив' ),
	'motiva'                                                                                  => array( 'designs', 'мотива' ),
	'Paket je pun — ukloni motiv da dodaš drugi'                                              => array( 'The package is full — remove a design to add another', 'Набор заполнен — удали мотив, чтобы добавить другой' ),
	'Izaberi još %d — paket nije popunjen'                                                    => array( "Choose %d more — the package isn't full", 'Выбери ещё %d — набор не заполнен' ),
	'Ukloni motiv'                                                                            => array( 'Remove design', 'Удалить мотив' ),
	'Dodajem…'                                                                                => array( 'Adding…', 'Добавляем…' ),
	'Dodavanje nije uspelo — pokušaj ponovo'                                                  => array( "Couldn't add to the cart — please try again", 'Не удалось добавить в корзину — попробуйте ещё раз' ),
	'U tvom domu'                                                                             => array( 'In your home', 'У тебя дома' ),
	'Tvoj kutak, malo mekši'                                                                  => array( 'Your corner, a little softer', 'Твой уголок, чуть мягче' ),
	'Pored lavabo, na kuki ili na polici — peškirići se uklope u svaki dom i unesu trunku topline.' => array( 'By the sink, on a hook or a shelf — the towels fit into any home and add a touch of warmth.', 'У раковины, на крючке или на полке — полотенца впишутся в любой дом и добавят капельку тепла.' ),
	'Spremni za jutarnju rutinu'                                                              => array( 'Ready for the morning routine', 'Готовы к утренним делам' ),
	'Na kuki, uvek pri ruci'                                                                  => array( 'On the hook, always at hand', 'На крючке, всегда под рукой' ),
	'Stiže spremno za poklon'                                                                 => array( 'Arrives gift-ready', 'Приходит готовым к подарку' ),
	'Svaki paket je mali poklon'                                                              => array( 'Every package is a little gift', 'Каждый набор — маленький подарок' ),
	'Pažljivo upakovano u CosyPaw kutiju, sa porukom dobrodošlice i mirisom lavande. Trio paket stiže uz <strong>besplatnu dostavu</strong> — idealno za rođendan, bebi šauer ili samo da nekog razmaziš.' => array( 'Carefully packed in a CosyPaw box, with a welcome note and a scent of lavender. The Trio package arrives with <strong>free shipping</strong> — perfect for a birthday, baby shower, or just to spoil someone.', 'Аккуратно упаковано в коробку CosyPaw, с приветственной открыткой и ароматом лаванды. Набор Trio приходит с <strong>бесплатной доставкой</strong> — идеально на день рождения, бэби-шауэр или просто чтобы кого-то побаловать.' ),
	'Jezik'                                                                                   => array( 'Language', 'Язык' ),
	'Komentari (%s)'                                                                          => array( 'Comments (%s)', 'Комментарии (%s)' ),
	'Prethodni'                                                                               => array( 'Previous', 'Предыдущие' ),
	'Sledeći'                                                                                 => array( 'Next', 'Следующие' ),
	'Komentari su zatvoreni.'                                                                 => array( 'Comments are closed.', 'Комментарии закрыты.' ),
	'Ostavi komentar'                                                                         => array( 'Leave a comment', 'Оставить комментарий' ),
	'Pošalji'                                                                                 => array( 'Send', 'Отправить' ),
	// Social proof (Utisci).
	'Zadovoljne mušterije'                                                                    => array( 'Happy customers', 'Довольные покупатели' ),
	'Mali peškirići, veliki osmesi'                                                           => array( 'Little towels, big smiles', 'Маленькие полотенца, большие улыбки' ),
	'Stigli su brže nego što sam očekivala i mekši su nego na slikama. Ćerka bira koji će da koristi svaki dan.' => array( 'They arrived faster than I expected and are softer than in the photos. My daughter picks which one to use every day.', 'Пришли быстрее, чем я ожидала, и мягче, чем на фото. Дочка сама выбирает, какое использовать каждый день.' ),
	'Kupila sam Trio paket za poklon i bio je pravi hit. Pakovanje je preslatko, ne moraš ništa dodatno da uvijaš.' => array( 'I bought the Trio package as a gift and it was a real hit. The packaging is adorable, no extra wrapping needed.', 'Купила набор Trio в подарок — и это был настоящий хит. Упаковка очаровательна, ничего дополнительно заворачивать не нужно.' ),
	'Alka za kačenje je sitnica koja mnogo znači — peškirić je uvek na svom mestu i ne završi na podu.' => array( 'The hanging loop is a small thing that means a lot — the towel is always in its place and never ends up on the floor.', 'Петелька — мелочь, которая много значит: полотенце всегда на своём месте и не падает на пол.' ),
	'5 od 5 zvezdica'                                                                         => array( '5 out of 5 stars', '5 из 5 звёзд' ),
	'Jovana M.'                                                                               => array( 'Jovana M.', 'Йована М.' ),
	'Milica P.'                                                                               => array( 'Milica P.', 'Милица П.' ),
	'Ana T.'                                                                                  => array( 'Ana T.', 'Ана Т.' ),
	'Novi Sad'                                                                                => array( 'Novi Sad', 'Нови-Сад' ),
	'Beograd'                                                                                 => array( 'Belgrade', 'Белград' ),
	'Niš'                                                                                     => array( 'Niš', 'Ниш' ),
	// FAQ.
	'Česta pitanja'                                                                           => array( 'FAQ', 'Частые вопросы' ),
	'Sve što te zanima'                                                                       => array( 'Everything you want to know', 'Всё, что вас интересует' ),
	'Ako ne nađeš odgovor, piši nam — rado pomažemo.'                                         => array( "If you don't find an answer, write to us — we're happy to help.", 'Если не найдёте ответ — напишите нам, мы рады помочь.' ),
	'Od čega su peškirići napravljeni?'                                                       => array( 'What are the towels made of?', 'Из чего сделаны полотенца?' ),
	'Od plišane mikrofibre — mekane, lagane i jako upijajuće. Prijatna je i nežnoj dečjoj koži.' => array( 'From plush microfiber — soft, light and highly absorbent. Gentle even on a child\'s skin.', 'Из плюшевой микрофибры — мягкой, лёгкой и очень впитывающей. Приятна даже нежной детской коже.' ),
	'Kako se peru?'                                                                           => array( 'How are they washed?', 'Как их стирать?' ),
	'Mašinsko pranje na 40°C, bez omekšivača da ostanu upijajući. Suše se brzo i ne gube oblik.' => array( 'Machine wash at 40°C, without fabric softener so they stay absorbent. They dry fast and keep their shape.', 'Машинная стирка при 40°C, без кондиционера, чтобы сохранить впитываемость. Быстро сохнут и держат форму.' ),
	'Koliko traje dostava?'                                                                   => array( 'How long does delivery take?', 'Сколько занимает доставка?' ),
	'Dostava je 2–4 radna dana na teritoriji cele Srbije. Trio paket stiže uz besplatnu dostavu.' => array( 'Delivery is 2–4 business days across Serbia. The Trio package comes with free shipping.', 'Доставка 2–4 рабочих дня по всей Сербии. Набор Trio — с бесплатной доставкой.' ),
	'Kako mogu da platim?'                                                                    => array( 'How can I pay?', 'Как я могу оплатить?' ),
	'Plaćanje je pouzećem — platiš kuriru pri preuzimanju paketa.'                            => array( 'Payment is cash on delivery — you pay the courier when you receive the package.', 'Оплата при получении — вы платите курьеру при получении посылки.' ),
	'Mogu li da vratim proizvod?'                                                             => array( 'Can I return a product?', 'Могу ли я вернуть товар?' ),
	'Naravno. Imaš 14 dana da vratiš nekorišćen peškirić uz povraćaj novca.'                  => array( 'Of course. You have 14 days to return an unused towel for a refund.', 'Конечно. У вас есть 14 дней, чтобы вернуть неиспользованное полотенце с возвратом денег.' ),
);

// English-source msgid => [ serbian, russian ].
$en_source = array(
	'Nothing found.'                                                                                           => array( 'Ništa nije pronađeno.', 'Ничего не найдено.' ),
	'Primary Sidebar'                                                                                          => array( 'Glavna bočna traka', 'Основной сайдбар' ),
	'Appears beside posts and archives.'                                                                       => array( 'Pojavljuje se pored objava i arhiva.', 'Отображается рядом с записями и архивами.' ),
	'Primary Menu'                                                                                             => array( 'Glavni meni', 'Основное меню' ),
	'Footer Menu'                                                                                              => array( 'Meni u podnožju', 'Меню в подвале' ),
	'CosyPaw recommends the WooCommerce plugin. Shop features are disabled until it is installed and active.'  => array( 'CosyPaw preporučuje WooCommerce dodatak. Funkcije prodavnice su onemogućene dok se ne instalira i aktivira.', 'CosyPaw рекомендует плагин WooCommerce. Функции магазина отключены, пока он не установлен и не активирован.' ),
	'CosyPaw Seeder'                                                                                           => array( 'CosyPaw Seeder', 'CosyPaw Seeder' ),
	'Done — %d new product(s) created.'                                                                        => array( 'Gotovo — kreirano %d novih proizvoda.', 'Готово — создано %d новых товаров.' ),
	'Currently mapped: %1$d motif products, %2$d package products.'                                            => array( 'Trenutno mapirano: %1$d proizvoda motiva, %2$d proizvoda paketa.', 'Сейчас сопоставлено: %1$d товаров-мотивов, %2$d товаров-наборов.' ),
	'Create / update CosyPaw products'                                                                         => array( 'Kreiraj / ažuriraj CosyPaw proizvode', 'Создать / обновить товары CosyPaw' ),
	'Safe to run repeatedly — existing products are skipped.'                                                  => array( 'Bezbedno za ponovno pokretanje — postojeći proizvodi se preskaču.', 'Можно запускать повторно — существующие товары пропускаются.' ),
	'You are not allowed to do this.'                                                                          => array( 'Nije vam dozvoljeno da ovo uradite.', 'У вас нет прав для этого действия.' ),
	'WooCommerce is not active.'                                                                               => array( 'WooCommerce nije aktivan.', 'WooCommerce не активен.' ),
	'This site cannot process AVIF images, and the CosyPaw motif photography ships in that format. Seeding will create the products but their images will be missing or thumbnail-less. AVIF needs WordPress 6.5 or newer plus GD/Imagick built with AVIF support — ask your host to enable it, then run the seeder again.' => array( 'Ovaj sajt ne može da obradi AVIF slike, a CosyPaw fotografije motiva su u tom formatu. Seeder će kreirati proizvode, ali će njihove slike nedostajati ili biti bez sličica. AVIF zahteva WordPress 6.5 ili noviji i GD/Imagick sa AVIF podrškom — zatraži od hostinga da je uključi, pa ponovo pokreni seeder.', 'Этот сайт не может обрабатывать изображения AVIF, а фотографии мотивов CosyPaw поставляются именно в этом формате. Seeder создаст товары, но их изображения будут отсутствовать или остаться без миниатюр. Для AVIF нужен WordPress 6.5 или новее и GD/Imagick с поддержкой AVIF — попроси хостинг включить её и запусти seeder снова.' ),
	'CosyPaw — names'                                                                                          => array( 'CosyPaw — imena', 'CosyPaw — названия' ),
	'The title field above is the Serbian name. Fill these in to set the name shown to English and Russian visitors.' => array( 'Polje za naslov iznad je srpski naziv. Popuni ova polja da postaviš naziv koji vide posetioci na engleskom i ruskom.', 'Поле заголовка выше — сербское название. Заполни эти поля, чтобы задать название для англоязычных и русскоязычных посетителей.' ),
	'Leave empty to use the translation from the theme language files (shown greyed out).'                     => array( 'Ostavi prazno da se koristi prevod iz jezičkih fajlova teme (prikazan sivo).', 'Оставь пустым, чтобы использовать перевод из языковых файлов темы (показан серым).' ),
	// WooCommerce page titles (DB content, translated via the_title for the WC page IDs).
	'Cart'                                                                                                     => array( 'Korpa', 'Корзина' ),
	'Checkout'                                                                                                 => array( 'Plaćanje', 'Оформление заказа' ),
	'Shop'                                                                                                     => array( 'Prodavnica', 'Магазин' ),
	'My account'                                                                                               => array( 'Moj nalog', 'Мой аккаунт' ),
);

// Assemble per-locale maps.
$en_US = array();
$ru_RU = array();
$sr_RS = array();
foreach ( $sr_source as $id => $t ) {
	$en_US[ $id ] = $t[0];
	$ru_RU[ $id ] = $t[1];
}
foreach ( $en_source as $id => $t ) {
	$sr_RS[ $id ] = $t[0];
	$ru_RU[ $id ] = $t[1];
}

$dir = dirname( __DIR__ ) . '/languages';
if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0755, true );
}

write_locale( $dir, 'en_US', $en_US );
write_locale( $dir, 'ru_RU', $ru_RU );
write_locale( $dir, 'sr_RS', $sr_RS );

echo 'Built: en_US (' . count( $en_US ) . '), ru_RU (' . count( $ru_RU ) . '), sr_RS (' . count( $sr_RS ) . ")\n";

/**
 * Write both .po and .mo for one locale.
 *
 * @param string               $dir    Languages directory.
 * @param string               $locale Locale code.
 * @param array<string,string> $map    msgid => msgstr.
 */
function write_locale( string $dir, string $locale, array $map ): void {
	$map = array_filter( $map, static fn( $v ) => '' !== $v );

	$header = "Content-Type: text/plain; charset=UTF-8\nLanguage: {$locale}\nMIME-Version: 1.0\nContent-Transfer-Encoding: 8bit\n";

	// --- .po ---
	$po = "msgid \"\"\nmsgstr \"\"\n";
	foreach ( explode( "\n", trim( $header ) ) as $line ) {
		$po .= '"' . po_escape( $line ) . '\n"' . "\n";
	}
	$po .= "\n";
	foreach ( $map as $id => $str ) {
		$po .= 'msgid "' . po_escape( $id ) . "\"\n";
		$po .= 'msgstr "' . po_escape( $str ) . "\"\n\n";
	}
	file_put_contents( "{$dir}/{$locale}.po", $po );

	// --- .mo (binary) ---
	$entries     = $map;
	$entries[''] = $header; // gettext metadata header.
	ksort( $entries, SORT_STRING );

	$ids  = array_keys( $entries );
	$n    = count( $entries );
	$base = 28 + $n * 8 + $n * 8; // header + original table + translation table (hash size 0).

	$orig_tbl  = '';
	$trans_tbl = '';
	$orig_buf  = '';
	$trans_buf = '';

	foreach ( $ids as $id ) {
		$orig_tbl .= pack( 'VV', strlen( $id ), $base + strlen( $orig_buf ) );
		$orig_buf .= $id . "\0";
	}
	$tbase = $base + strlen( $orig_buf );
	foreach ( $ids as $id ) {
		$str        = $entries[ $id ];
		$trans_tbl .= pack( 'VV', strlen( $str ), $tbase + strlen( $trans_buf ) );
		$trans_buf .= $str . "\0";
	}

	$mo  = pack( 'V', 0x950412de ); // magic (little-endian).
	$mo .= pack( 'V', 0 );          // revision.
	$mo .= pack( 'V', $n );         // string count.
	$mo .= pack( 'V', 28 );         // original table offset.
	$mo .= pack( 'V', 28 + $n * 8 );// translation table offset.
	$mo .= pack( 'V', 0 );          // hash size.
	$mo .= pack( 'V', 28 + $n * 8 + $n * 8 ); // hash offset.
	$mo .= $orig_tbl . $trans_tbl . $orig_buf . $trans_buf;

	file_put_contents( "{$dir}/{$locale}.mo", $mo );
}

/**
 * Escape a string for a .po literal.
 *
 * @param string $s Raw string.
 * @return string
 */
function po_escape( string $s ): string {
	return str_replace( array( '\\', '"', "\n" ), array( '\\\\', '\\"', '\\n' ), $s );
}
