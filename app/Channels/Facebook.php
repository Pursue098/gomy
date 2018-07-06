<?php

namespace App\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
use SammyK\LaravelFacebookSdk\SyncableGraphNodeTrait;
use Facebook\Exceptions\FacebookSDKException;
use App\Jobs\GrabChannelFacebook;
use App\Channels\Channable;
use App\Channels\Facebook\User;
use App\Channels\Facebook\Post;
use App\Project;
use App\Channel;
use App\CRM;

class Facebook extends Model implements Channable
{
    use SyncableGraphNodeTrait;

    public $incrementing = false;

    public static $index = 'facebook2';

    protected $table = 'ch_facebook';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'access_token', 'app_id', 'app_secret',
    ];

    public function channel()
    {
        return $this->morphOne('App\Channel', 'channable');
    }

    public function picture() {
        if ($this->getAttributeValue('picture') !== null) {
            return $this->picture;
        }

        return '//graph.facebook.com/' . $this->id . '/picture?width=160&amp;height=160';
    }

    public function social_login_points() {
        return $this->coefficient_social_login;
    }

    public static function fb_founded(\Facebook\GraphNodes\GraphNode $page) {
        if (isset($page['start_info']) && isset($page['start_info']['date'])) {
            $date = $page['start_info']['date'];
            return date('Y-m-d', strtotime(implode('-', $date->all())));
        }

        if (isset($page['founded'])) {
            $page['founded'] = str_replace([
                'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottombre', 'Novembre', 'Dicembre'
            ], [
                'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
            ], $page['founded']);

            return date('Y-m-d', strtotime($page['founded']));
        }
    }

    /**************************************************************************************************
     *                                            GRABBING
     **************************************************************************************************/



    /**************************************************************************************************
     *                                             WEBHOOKS
     **************************************************************************************************/

}
