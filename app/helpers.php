<?php
function name_case($string)
{
    $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
    $lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
    $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

    $string = strtolower($string);
    foreach ($word_splitters as $delimiter)
    {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ($words as $word)
        {
            if (in_array(strtoupper($word), $uppercase_exceptions))
                $word = strtoupper($word);
            else
            if (!in_array($word, $lowercase_exceptions))
                $word = ucfirst($word);

            $newwords[] = $word;
        }

        if (in_array(strtolower($delimiter), $lowercase_exceptions))
            $delimiter = strtolower($delimiter);

        $string = join($delimiter, $newwords);
    }
    return $string;
}

function gravatar($email) {
    $hash = md5(strtolower(trim($email)));
    return '//www.gravatar.com/avatar/' . $hash . '?d=identicon';
}

function fakeid($id) {
    App::make('fakeid')->encode($id);
}

function elastic() {
    return app()->make(\Elasticsearch\Client::class);
}

function _n($n, $decimal = 0) {
    // if ($n > 99999) {
    //     return social_round($n, $decimal);
    // }

    return number_format($n, $decimal, ',', '.');
}

function social_round($n, $decimal = 0) {
    if ($n < 1000) {
        return round($n, 0);
    }
    else if ($n > 1000000) {
        return round(($n/ 1000000), $decimal) . 'M';
    }
    else {
        return round(($n/ 1000), $decimal) . 'K';
    }
}

function geocode($address) {
    if (empty(trim($address))) {
        return null;
    }

    $geo = app()->make(\GoogleMapsGeocoder::class);

    $geo->setAddress($address);

    try {
        $resp = $geo->geocode();
    } catch(ErrorException $e) {
        return null;
    }

    if ($resp['status'] != 'OK') {
        return null;
    }

    $address = [
        'city' => [],
    ];

    //return $resp['results'];

    foreach($resp['results'][0]['address_components'] as $component) {
        if (in_array('street_number', $component['types'])) {
            $address['number'] = $component['long_name'];
        }

        if (in_array('route', $component['types'])) {
            $address['street'] = $component['long_name'];
        }

        if (in_array('postal_town', $component['types'])) {
            $address['city'][] = $component['long_name'];
        }

        if (in_array('locality', $component['types'])) {
            $address['city'][] = $component['long_name'];
        }

        if (in_array('administrative_area_level_3', $component['types'])) {
            $address['city'][] = $component['long_name'];
        }

        if (in_array('administrative_area_level_2', $component['types'])){
            $address['county'] = $component['long_name'];
            $address['county_code']  = $component['short_name'];
        }

        if (in_array('administrative_area_level_1', $component['types'])) {
            $address['state'] = $component['long_name'];
            $address['state_code'] = $component['short_name'];
        }

        if (in_array('postal_code', $component['types'])) {
            $address['postal_code'] = $component['long_name'];
        }

        if (in_array('country', $component['types'])){
            $address['country'] = $component['long_name'];
            $address['country_code']  = $component['short_name'];
        }
    }

    if (! empty($address['city'])) {
        $address['city'] = array_unique($address['city'])[0];
    }

    $address['location'] = [
        'lat' => $resp['results'][0]['geometry']['location']['lat'],
        'lon' => $resp['results'][0]['geometry']['location']['lng'],
    ];

    $address['formatted'] = $resp['results'][0]['formatted_address'];

    return $address;
}

function file_get_contents_retry($url, $attempt = 0, $as_array = false) {
    if ($attempt > 9) {
        throw new Exception("Max attempt: $url", 1);
    }

    try {
        $context = stream_context_create(array(
            'http' => array (
                'ignore_errors' => TRUE
             )
        ));

        $resp = json_decode(file_get_contents($url, false, $context), $as_array);

        if (isset($resp->error)) {
            if ($resp->error->message == '(#32) Page request limited reached') {
                \Log::info('(#32) Page request limited reached.');
                \Log::info('URL: ' . $url);
            } else if ($resp->error->message == 'An unexpected error has occurred. Please retry your request later.') {
                \Log::info('FB: An unexpected error has occurred. Try again: ' . $attempt);
                \Log::info('URL: ' . $url);
                return file_get_contents_retry($url, ++$attempt, $as_array);
            } else if ($resp->error->message == 'Error validating access token: The session has been invalidated because the user changed their password or Facebook has changed the session for security reasons.') {
                \Log::error($resp->error->message);

                \Mail::raw($resp->error->message, function ($message) {
                    $message->to('stefano.colao@teia.company');
                    $message->subject('Cyrano token invalidated');
                });

                $fb = app(\SammyK\LaravelFacebookSdk\LaravelFacebookSdk::class);

                $app_token = config('laravel-facebook-sdk.facebook_config.app_id') . '|' . config('laravel-facebook-sdk.facebook_config.app_secret');

                $_url = parse_url($url);
                parse_str($_url['query'], $query);

                $token = $fb->get('/debug_token?input_token=' . $query['access_token'], $app_token);

                $token = json_decode($token->getGraphNode());

                if (isset($token->type) && $token->type == 'PAGE' && isset($token->profile_id) && ! empty($token->profile_id)) {
                    \Log::error('Unsubscribing page: ' . $token->profile_id);
                    $fb->delete('/' . $token->profile_id . '/subscribed_apps', [], $app_token);

                    \Mail::raw('Page ' . $token->profile_id . ' unsubscribed.', function ($message) {
                        $message->to('stefano.colao@teia.company');
                        $message->subject('Cyrano page unsubscribed');
                    });
                }
            }
        }

        return $resp;
    } catch (ErrorException $e) {
        sleep(2**$attempt);
        return file_get_contents_retry($url, ++$attempt, $as_array);
    }
}

function scramble_word($word) {
    $words = explode(' ', $word);

    foreach($words as $i => $word) {
        $words[$i] = $word{0} . str_shuffle(substr($word, 1, -1)) . $word{strlen($word) - 1};
    }

    return implode(' ', $words);
}

function handle_download_error($error, $return_url) {
    $host = request()->server('HTTP_HOST');

    if (isset($return_url) && $return_url != null) {
        return redirect($return_url);
    }

    $back = parse_url(url()->previous(), PHP_URL_HOST);

    if (isset($back) && ! empty($back) && $host != $back) {
        return redirect()->back();
    }

    return $error;
}

function formatPhone ($phone, $dialCode, $country) {

    $phoneUtil = libphonenumber\PhoneNumberUtil::getInstance();

    if ( $phone[0] != '+') {
        $phoneSTR = '+' . $dialCode . $phone;
    } else {
        $phoneSTR = $phone;
    }

    if ($phoneUtil->isPossibleNumber($phoneSTR, $country)) {
        $p = $phoneUtil->parse($phoneSTR, $country);
        if ($phoneUtil->isValidNumber($p)){
            return phone($phoneSTR, $country, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
        } else {
            return 401;
        }
    } else {
        return 401;
    }

}
