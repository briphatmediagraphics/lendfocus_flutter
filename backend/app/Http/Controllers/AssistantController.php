<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function index(Request $request)
    {
        return view('public.assistant', [
            'answer' => session('assistant_answer'),
        ]);
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $lang = session('lang', 'sw');
        $answer = $this->answer($data['question'], $lang);

        return redirect()->route('assistant')->with('assistant_answer', $answer);
    }

    protected function answer(string $question, string $lang): string
    {
        $q = mb_strtolower(trim($question));
        $company = config('landfocus.company');
        $services = config('landfocus.services');

        if (str_contains($q, 'huduma') || str_contains($q, 'service')) {
            $list = collect($services)->take(6)->map(fn ($s) => $lang === 'en' ? $s['title_en'] : $s['title_sw'])->implode("\n• ");

            return ($lang === 'en'
                ? "LandFocus offers these services:\n• "
                : "LandFocus inatoa huduma zifuatazo:\n• ").$list;
        }

        if (str_contains($q, 'ombi') || str_contains($q, 'request') || str_contains($q, 'book')) {
            return $lang === 'en'
                ? 'You can submit a request via the Book page (/book) or Contact form (/contact). Our team will respond shortly.'
                : 'Unaweza kutuma ombi kupitia ukurasa wa Ombi la Miradi (/book) au fomu ya Mawasiliano (/contact). Timu yetu itawasiliana nawe hivi karibuni.';
        }

        if (str_contains($q, 'ofisi') || str_contains($q, 'office') || str_contains($q, 'wapi') || str_contains($q, 'where')) {
            return ($lang === 'en' ? 'Our office is at Tegeta Complex. ' : 'Ofisi zetu ziko Tegeta Complex. ')
                .($lang === 'en' ? 'You can also reach us at ' : 'Unaweza pia kutupata kupitia ')
                .$company['phone'];
        }

        if (str_contains($q, 'simu') || str_contains($q, 'phone') || str_contains($q, 'contact') || str_contains($q, 'wasiliana')) {
            return ($lang === 'en' ? 'Contact us at ' : 'Wasiliana nasi: ')
                .$company['phone']
                .' | '
                .$company['email'];
        }

        if (str_contains($q, 'miradi') || str_contains($q, 'propert') || str_contains($q, 'ardhi') || str_contains($q, 'land')) {
            return $lang === 'en'
                ? 'Browse available projects at /properties — land plots, houses, farms and offices across Tanzania.'
                : 'Tazama miradi zilizopo kwenye /properties — vipande vya ardhi, nyumba, mashamba na ofisi nchini Tanzania.';
        }

        return $lang === 'en'
            ? 'Thank you for your question. For detailed help, contact us at '.$company['phone'].' or visit /contact.'
            : 'Asante kwa swali lako. Kwa msaada zaidi, wasiliana nasi kupitia '.$company['phone'].' au tembelea /contact.';
    }
}
