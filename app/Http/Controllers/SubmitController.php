<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubmitController extends Controller
{
    public function submitContactForm(Request $request) {
        $toValidate = array(
            'Naam' => 'required',
            'E-mail_adres' => 'required|email',
            'Bericht' => 'required',
        );
        $validationMessages = array(
            'Naam.required'=> 'Please provide a name',
            'E-mail_adres.required'=> 'Please provide an e-mail address',
            'E-mail_adres.email'=> 'The e-mail address is not correctly formed',
            'Bericht.required'=> 'Please fill in a message',
        );
        /***********************************************************************************
            Gebruik maken van manually created validator ($validated = $request->validate($toValidate,$validationMessages)
                OF gebruik van redirect()->back() / url()->previous() WERKT NIET!!! ---> strict-origin-when-cross-origin (referrer-policy)
            alleen redirect('/contact') gebruiken (bijvoorbeeld)
                OF bij dynamische paginas de huidige gebruiken: url()->current()
        ************************************************************************************/
        // $validated = $request->validate($toValidate,$validationMessages);
        $validator = Validator::make($request->all(), $toValidate, $validationMessages);
        if($validator->fails()) {
            return redirect('/contact')->withErrors($validator)->withInput();
            // return redirect()->back()->withErrors($validator)->withInput();
            // return back()->withErrors($validator)->withInput();
        }

        $to_email = 'leon@wtmedia-events.nl';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        // $subject = 'Ingevuld contactformulier vanaf rsmarine.eu';
        $subjectCompany = 'Ingevuld contactformulier vanaf rsmarine.eu';
        $subjectVisitor = 'Copy of your message to rsmarine.eu';
        
        $messages = $this->getHtmlEmails($request->all(), 'https://rsmarine.wtgroup.nl/statics/email/logo.jpg', 'De volgende gegevens zijn achtergelaten door de bezoeker.', 'Thanks for your message. We received the following information:');

        $headers = array(
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "From: RS Marine <contactform@rsmarine.eu>",
            "Reply-To: info@rsmarine.eu",
            // "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        mail($to_email, $subjectCompany, $messages[0], $headers);
        mail($request->get('E-mail_adres'), $subjectVisitor, $messages[1], $headers);
        // mail($to_email, $subject, $message, $headers);
        // mail($to_email, $subject, $message);
        // return back()->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
        return redirect('/contact')->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
    }
    public function submitApplyForm(Request $request) {
        $toValidate = array(
            'Voornaam' => 'required',
            'Achternaam' => 'required',
            'E-mail_adres' => 'required|email',
            // 'Bericht' => 'required',
        );
        $validationMessages = array(
            'Voornaam.required'=> 'Geef een voornaam op.',
            'Achternaam.required'=> 'Geef een achternaam op.',
            'E-mail_adres.required'=> 'Geef een e-mail adres op.',
            'E-mail_adres.email'=> 'Het e-mail adres is niet juist geformuleerd.',
            // 'Bericht.required'=> 'Vul een bericht in.',
        );
        /***********************************************************************************
            Gebruik maken van manually created validator ($validated = $request->validate($toValidate,$validationMessages)
                OF gebruik van redirect()->back() / url()->previous() WERKT NIET!!! ---> strict-origin-when-cross-origin (referrer-policy)
            alleen redirect('/contact') gebruiken (bijvoorbeeld)
                OF bij dynamische paginas de huidige gebruiken: url()->current()
        ************************************************************************************/
        // $validated = $request->validate($toValidate,$validationMessages);
        $validator = Validator::make($request->all(), $toValidate, $validationMessages);
        if($validator->fails()) {
            return redirect(url()->current())->withErrors($validator)->withInput();
            // return redirect()->back()->withErrors($validator)->withInput();
            // return back()->withErrors($validator)->withInput();
        }

        $to_email = 'leon@wtmedia-events.nl';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        // $subject = 'Ingevuld contactformulier vanaf rsmarine.eu';
        $subjectCompany = 'Sollicitatie vanaf rsmarine.eu';
        $subjectVisitor = 'Kopie van je sollicitatie aan RS Marine';
        
        $messages = $this->getHtmlEmails($request->all(), 'https://rsmarine.wtgroup.nl/statics/email/logo.jpg', 'De volgende gegevens zijn achtergelaten door de bezoeker.', 'Bedankt voor je sollicitatie. De volgende informatie hebben wij ontvangen:');

        $headers = array(
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "From: RS Marine <sollicitatieformulier@rsmarine.eu>",
            "Reply-To: info@rsmarine.eu",
            // "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        mail($to_email, $subjectCompany, $messages[0], $headers);
        mail($request->get('E-mail_adres'), $subjectVisitor, $messages[1], $headers);
        // mail($to_email, $subject, $message, $headers);
        // mail($to_email, $subject, $message);
        // return back()->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
        return redirect(url()->current())->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
    }
    public function submitBestellenForm(Request $request) {
        $validated = $request->validate([
            'Betreft' => 'required',
            'Bedrijfsnaam' => 'required',
            'Contactpersoon' => 'required',
            'Emailadres' => 'required|email',
            'Bericht' => 'required',
        ],[
            'Betreft.required'=> 'Geef a.u.b. de reden van toenadering aan.',
            'Bedrijfsnaam.required'=> 'Geef a.u.b. een bedrijfsnaam op.',
            'Contactpersoon.required'=> 'Geef a.u.b. een contactpersoon op.',
            'Emailadres.required'=> 'Geef a.u.b. een e-mail adres op.',
            'Emailadres.email'=> 'Het e-mail adres is niet juist geformuleerd.',
            'Bericht.required'=> 'Er is geen bericht ingevoerd.',
        ]);

        $to_email = 'leon.kuijf@gmail.com';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        $subject = 'Ingevuld bestelformulier vanaf ......nl';
        $message = 'De volgende informatie is verzonden:
        
            Betreft: ' . $request->get('Betreft') . '
            Bedrijfsnaam: ' . $request->get('Bedrijfsnaam') . '
            Contactpersoon: ' . $request->get('Contactpersoon') . '
            Email adres: ' . $request->get('Emailadres') . '
            Bericht: ' . $request->get('Bericht') . '
            ';

        $headers = array(
            "From: bestelformulier@.....nl",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        // mail($to_email, $subject, $message, $headers);
        mail($to_email, $subject, $message);
        return back()->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
    }
    public function getHtmlEmails($values, $imgLocation, $introTextCompany, $introTextVisitor) {
        $message1 = '';
        $message2 = '';
        $topHtml = '
        <html><body>
        <!--[if mso]>
        <table cellpadding="0" cellspacing="0" border="0" style="padding:0px;margin:0px;width:100%;">
            <tr>
                <td style="padding:0px;margin:0px;">&nbsp;</td>
                <td style="padding:0px;margin:0px;" width="500">
        <![endif]-->
                    <div style="
                        max-width: 500px;
                        padding: 20px;
                        font-family: verdana, arial;
                        font-size: 14px;
                        margin-left: auto;
                        margin-right: auto;
                        background-color: #FFF;
                        border: 1px solid #CCC;
                    ">
                    <p style="text-align:center;"><img src="' . $imgLocation . '" alt="JusBros logo" /></p>
        ';

        $messageCompany = '';
        $messageVisitor = '';
        foreach($values as $i => $v) {
            if($i == '_token' || $i == 'g-recaptcha-response') continue;
            $messageCompany .= '
            <p>
                ' . str_replace('_', ' ', $i) . ':<br />
                <strong>' . (trim($v) == ''?'-':$v) . '</strong>
            </p>
            ';
            if($i == 'g-recaptcha-score') continue;
            $messageVisitor .= '
            <p>
                ' . str_replace('_', ' ', $i) . ':<br />
                <strong>' . (trim($v) == ''?'-':$v) . '</strong>
            </p>
            ';
        }
        $bottomHtml = '';
        $bottomHtml .= '
                    </div>
        <!--[if mso]>
                </td>
                <td style="padding:0px;margin:0px;">&nbsp;</td>
            </tr>
        </table>
        <![endif]-->
        </body></html>
        ';

        $message1 = $topHtml . '<p>' . $introTextCompany . '</p>' . $messageCompany . $bottomHtml;
        $message2 = $topHtml . '<p>' . $introTextVisitor . '</p>' . $messageVisitor . $bottomHtml;

        return array($message1, $message2);
    }
    function writeToFile($file, $values) {
        $aLine = [];
        $aLine[] = date('Y-m-d H:i:s');
        if(trim(file_get_contents($file)) == '') file_put_contents($file, 'Tijdstip reservering;Naam;Bedrijfsnaam;E-mail adres;Telefoon;Aantal personen;Aanvullende wensen' . "\n");
        foreach($values as $i => $v) {
            if($i == '_token' || $i == 'g-recaptcha-response') continue;
            $v = Str::of($v)->trim();
            $v = Str::replace(';', ':', $v);
            // $v = Str::replace(array("\r", "\n"), '', $v);
            $v = trim(preg_replace('/\s\s+/', ' ', $v)); //https://stackoverflow.com/questions/3760816/remove-new-lines-from-string-and-replace-with-one-empty-space
            $aLine[] = trim($v);
        }
        file_put_contents($file, implode(';', $aLine) . "\n", FILE_APPEND);
    }
}