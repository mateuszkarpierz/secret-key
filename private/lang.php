<?php
// ════════════════════════════════════════════════════════
//  lang.php — teksty interfejsu Secret Key
//  Plik poza public_html: /private/lang.php
//
//  UWAGA: to NIE jest system wielojęzyczny z przełącznikiem.
//  Jeden język na cały system, ustawiany raz przez edycję tego pliku.
//  Jeśli chcesz uruchomić system po angielsku (albo w innym języku),
//  po prostu przetłumacz wartości poniżej — całość, łącznie z treścią
//  w private/secret-key.php ($instructions, $download_intro, itd.),
//  bo to Twoja własna treść dla Twoich bliskich, nie generyczny UI.
//
//  Brakujący klucz nie wysypuje strony — t() zwraca wtedy sam klucz
//  jako widoczny placeholder (patrz funkcja t() w key/auth.php).
// ════════════════════════════════════════════════════════

$lang = [

    // ── META — atrybut <html lang="..."> (zmień razem z resztą pliku!) ──
    '_html_lang' => 'pl',

    // ── WSPÓLNE (login.php + decrypt/index.php — jokescreen antydebugowy) ──
    'common_error_generic'        => 'Wystąpił błąd.',
    'common_error_connection'     => 'Błąd połączenia. Spróbuj ponownie.',
    'common_error_bad_token'      => 'Nieprawidłowy token. Odśwież stronę.',

    'jokescreen_title'            => 'Naruszenie bezpieczeństwa',
    'jokescreen_ref_prefix'       => 'REF #',
    'jokescreen_access_halted'    => 'Dostęp wstrzymany',
    'jokescreen_subtitle'         => 'Wykryto próbę inspekcji chronionego zasobu.',
    'jokescreen_subtitle_2'       => 'Sesja została wstrzymana do czasu zamknięcia narzędzi deweloperskich.',
    'jokescreen_violations_label' => 'Zarejestrowane naruszenia',
    'jokescreen_violation_1'      => 'Inspekcja kodu źródłowego',
    'jokescreen_violation_2'      => 'Panel deweloperski aktywny',
    'jokescreen_violation_3'      => 'Debugowanie sesji użytkownika',
    'jokescreen_detected_tag'     => 'WYKRYTO',
    'jokescreen_time_label'       => 'Czas naruszenia',
    'jokescreen_footer_active'    => 'Ochrona aktywna',
    'jokescreen_close_hint'       => 'zamknij devtools → strona wróci',

    // ── LOGIN.PHP — krok 1 (login + hasło) ──
    'login_page_title'            => 'Logowanie — Secret Key',
    'login_session_expired'       => 'Sesja wygasła z powodu nieaktywności.',
    // UWAGA: poniższe klucze (login_card_intro, login_hint_box, login_footer_restricted)
    // renderują się BEZ htmlspecialchars() — świadomie zawierają znaczniki HTML
    // (<strong>, &nbsp;). Edytując zachowaj tagi/encje, albo usuń je całkiem —
    // nie wpisuj "<" ani "&" jako zwykłego tekstu (zepsuje wygląd, patrz błąd
    // z literalnym "&nbsp;" na stronie zamiast spacji — to jest ten przypadek).
    'login_card_intro'            => 'Znajdujesz się na tej stronie, ponieważ jesteś posiadaczem <strong>1&nbsp;z&nbsp;5&nbsp;części</strong> kodu Secret Key.',
    'login_hint_box'               => 'Dane do logowania znajdują się na Twojej karcie&nbsp;<strong>Secret Key</strong>.',
    'login_label_username'        => 'Login',
    'login_placeholder_username'  => 'Twój login z karty',
    'login_label_password'        => 'Hasło',
    'login_placeholder_password'  => 'Twoje hasło z karty',
    'login_show_password'         => 'Pokaż hasło',
    'login_submit_btn'            => 'Zaloguj się',
    'login_empty_fields'          => 'Wpisz login i hasło.',
    'login_welcome_back'          => 'Witaj ponownie, {name}…',
    'login_footer_restricted'     => 'obszar zastrzeżony &nbsp;·&nbsp; autoryzacja wymagana',

    // ── LOGIN.PHP — krok 2 (weryfikacja SMS) ──
    // UWAGA: te dwa klucze też renderują się BEZ htmlspecialchars() (patrz wyżej).
    'twofa_sent_to'               => 'Wysłano kod weryfikacyjny na&nbsp;',
    'twofa_intro'                 => 'Wpisz <strong>6-cyfrowy kod</strong> z wiadomości SMS, aby potwierdzić swoją tożsamość.',
    // UWAGA: treść wysyłanego SMS-a — celowo BEZ polskich znaków (ż/ą/ę/ć...).
    // SMS z choćby jednym znakiem spoza GSM-7 jest liczony/rozliczany przez
    // operatora jako dłuższy/droższy (mniejszy limit znaków w jednej wiadomości,
    // czasem podział na kilka SMS-ów). %s = kod, %d = ważność w minutach.
    'twofa_sms_body'              => 'Kod weryfikacyjny: %s. Wazny %d min. Nie udostepniaj go nikomu.',
    'twofa_remember_device'       => 'Zapamiętaj to urządzenie na 7 dni',
    'twofa_verify_btn'            => 'Weryfikuj',
    'twofa_verifying'             => 'Weryfikowanie…',
    'twofa_no_sms_question'       => 'Nie dostałeś SMS-a?',
    'twofa_resend_btn_waiting'    => 'Wyślij kod ponownie za (<span id="resend-timer">',
    'twofa_resend_btn_ready'      => 'Wyślij ponownie',
    'twofa_resend_sending'        => 'Wysłano kod...',
    'twofa_decrypting'            => 'Odszyfrowywanie…',
    'twofa_resend_unit_1'         => 'sekunda',
    'twofa_resend_unit_few'       => 'sekundy',
    'twofa_resend_unit_many'      => 'sekund',

    // ── VERIFY.PHP (odpowiedzi JSON przy weryfikacji kodu 2FA) ──
    'verify_bad_code_format'      => 'Podaj 6-cyfrowy kod.',
    'verify_invalid_code'         => 'Nieprawidłowy kod.',
    'verify_attempts_left_1'      => 'Pozostało %d próba.',
    'verify_attempts_left_few'    => 'Pozostało %d próby.',
    'verify_attempts_left_many'   => 'Pozostało %d prób.',
    'verify_expired'              => 'Kod wygasł. Zaloguj się ponownie.',
    'verify_blocked'              => 'Zbyt wiele błędnych prób. Zaloguj się ponownie.',
    'verify_ip_blocked'           => 'Zbyt wiele błędnych prób z tego urządzenia. Spróbuj ponownie za godzinę.',
    'verify_session_expired'      => 'Sesja wygasła. Zaloguj się ponownie.',

    // ── RESEND.PHP (ponowna wysyłka kodu 2FA) ──
    'resend_bad_session'          => 'Nieprawidłowa sesja. Odśwież stronę i zaloguj się ponownie.',
    'resend_session_expired'      => 'Sesja wygasła. Zaloguj się ponownie.',
    'resend_wait_seconds'         => 'Poczekaj jeszcze %d s przed ponownym wysłaniem.',
    'resend_limit_reached'        => 'Przekroczono limit wysyłania kodów. Zaloguj się ponownie.',
    'resend_config_error'         => 'Błąd konfiguracji.',
    'resend_sms_failed'           => 'Nie udało się wysłać SMS. Spróbuj za chwilę.',

    // ── AUTH.PHP (komunikaty logowania — attemptLogin) ──
    'auth_too_many_attempts'      => 'Zbyt wiele nieudanych prób. Spróbuj ponownie za 15 minut.',
    'auth_invalid_credentials'    => 'Nieprawidłowy login lub hasło. Sprawdź dane na swojej karcie Secret Key.',
    'auth_sms_send_failed'        => 'Nie udało się wysłać SMS. Spróbuj ponownie za chwilę.',

    // ── DECRYPT/INDEX.PHP — nagłówek i powitanie ──
    'panel_header_title'          => 'Panel Secret Key',
    'panel_welcome'                => 'Witaj, ',
    'panel_welcome_fallback_name' => 'Gość',
    'panel_logout_btn'            => 'Wyloguj',

    // ── DECRYPT/INDEX.PHP — instrukcja i lista posiadaczy ──
    'panel_instructions_title'    => 'Instrukcja dostępu do bazy haseł',
    'panel_persons_title'         => 'Lista posiadaczy Secret key',
    'panel_person_reveal_btn'     => 'odszyfruj',
    'panel_person_name_label'     => 'Imię i nazwisko',
    'panel_person_phone_label'    => 'Telefon',

    // ── DECRYPT/INDEX.PHP — panel odszyfrowywania ──
    'panel_decrypt_title'         => 'Odszyfrowywanie',
    'panel_decrypt_label'         => 'Wprowadź Secret key',
    'panel_decrypt_hint_link'     => 'Co to jest Secret key?',
    'panel_decrypt_placeholder'   => 'Wpisz swój kod z karty tutaj — jeden kod, jedna linia…',
    'panel_key_counter_label'     => 'KODY:',
    'panel_key_counter_required'  => 'wymagane',
    'panel_password_title_prefix' => 'Hasło do bazy ',
    'panel_decrypt_waiting'       => 'Czekam na kody… wpisz co najmniej 3, a hasło pojawi się w tym miejscu.',
    'panel_decrypt_error_prefix'  => 'Błąd: ',

    // ── DECRYPT/INDEX.PHP — modal "Co to jest Secret key?" ──
    'modal_secret_key_title'      => 'Co to jest Secret key?',
    'modal_secret_key_note'       => 'Secret key to specjalny ciąg znaków kryptograficznych, który znajduje się na odwrocie karty w miejscu zaznaczonym czerwoną obramówką. Jest on również umieszczony w kodzie QR.',

    // ── DECRYPT/INDEX.PHP — pasek informacji o sesji (stopka) ──
    'session_info_ip'             => 'IP',
    'session_info_browser'        => 'PRZEGLĄDARKA',
    'session_info_system'         => 'SYSTEM',
    'session_info_device'         => 'URZĄDZENIE',
    'session_info_screen'         => 'EKRAN',
    'session_info_language'       => 'JĘZYK',
    'session_info_timezone'       => 'STREFA CZASOWA',
    'session_info_logged_in'      => 'ZALOGOWANO',
    'session_info_session_time'   => 'CZAS SESJI',

    // ── DECRYPT/INDEX.PHP — treść maila powiadomienia o logowaniu ──
    'mail_subject_prefix'         => '🔐 Logowanie do Secret Key Panel — ',
    'mail_body_intro'             => 'Nowe logowanie do panelu Secret Key.',
    'mail_body_user_label'        => 'Użytkownik:   ',
    'mail_body_date_label'        => 'Data i czas:  ',
    'mail_body_ip_label'          => 'Adres IP:     ',
    'mail_body_browser_label'     => 'Przeglądarka: ',
    'mail_body_panel_label'       => 'Panel: ',

];
