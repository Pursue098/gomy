<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;


class LoyaltyMember
{
    use Notifiable;

    public static $index = 'loyalty';
    public static $type  = 'members';

    public function __construct(array $attributes = [])
    {
        foreach($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    public static function generate_pin() {
        $pin = sprintf("%06d", mt_rand(1, 999999));

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['pin' => hash('sha256', $pin)]]
                        ]
                    ]
                ]
            ]
        ];

        if (elastic()->count($params)['count'] > 0) {
            \Log::info('loyalty pin collision detected and avoided');

            return static::generate_pin();
        }

        return $pin;
    }

    public static function create($params) {
        $params['projects'] = [];

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'id'    => $params['uuid'],
            'body'  => $params
        ];

        return elastic()->index($params);
    }

    public function subscribe(\App\Project $project) {
        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'id'    => $this->uuid,
            'body'  => [
                'script' =>
                    'if(ctx._source.containsKey("projects")) {
                        def found = false;

                        ctx._source.projects?.eachWithIndex {
                            obj, i -> if (obj.id == project.id) {
                                found = true
                             }
                        };

                        if (! found) {
                            ctx._source.projects += project;
                        }
                    } else {
                        ctx._source.projects = [project];
                    }
                ',
                'params' => [
                    'project' => [
                        'id'            => $project->id,
                        'subscribed_at' => gmdate(DATE_ISO8601, time()),
                    ]
                ]
            ]
        ];

        return elastic()->update($params);
    }

    public function routeNotificationForNexmo()
    {
        return $this->phone;
    }

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }
}
