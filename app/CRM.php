<?php

namespace App;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Ramsey\Uuid\Uuid;

class CRM {
    public static $index = 'crm';
    public static $type = 'users';

    public static function generate_uuid() {
        $uuid = Uuid::uuid4()->toString();

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['uuid' => $uuid]]
                        ]
                    ]
                ]
            ]
        ];

        if (elastic()->count($params)['count'] > 0) {
            \Log::info('uuid collision detected and avoided');

            return static::generate_uuid();
        }

        return $uuid;
    }

    public static function is_user_in_project($uuid, $project) {
        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['uuid' => $uuid]],
                            ['term' => ['project' => $project]],
                        ]
                    ]
                ]
            ]
        ];

        return (elastic()->count($params)['count'] > 0);
    }

    public static function create_user($project, $uuid, array $channel) {
        $params = [
            'size' => 100,
            'body' => [
                '_source' => ['points'],
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['uuid' => $uuid]],
                            ['term' => ['projects' => $project]],
                        ]
                    ]
                ]
            ]
        ];

        $points = collect(elastic()->search($params)['hits']['hits'])->sum('_source.points');

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'id'    => $project . '_' . $uuid,
            'body'  => [
                'uuid'          => $uuid,
                'points'        => $points,
                'unused_points' => $points,
                //'projects'    => $projects,
                'project'       => $project,
                'channels'      => [
                    $channel
                ]
            ]
        ];

        elastic()->index($params);
    }

    public static function add_channel_to_user($project, $uuid, array $channel) {

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'id'    => $project . '_' . $uuid,
            'body'  => [
                //'script' => 'ctx._source.channels += channel; ctx._source.channels.unique()', //'; ctx._source.projects += projects; ctx._source.projects.unique()',
                'script' =>
                    'if(ctx._source.containsKey("channels")) {
                        def found = false;

                        ctx._source.channels?.eachWithIndex {
                            obj, i -> if (obj.type == channel.type && obj.channel_id == channel.channel_id && obj.user_id == channel.user_id) {
                                if (channel.size() > obj.size()) {
                                    ctx._source.channels[i] = channel
                                }

                                found = true
                             }
                        };

                        if (! found) {
                            ctx._source.channels += channel;
                        }
                    } else {
                        ctx._source.channels = [channel];
                    }
                ',
                'params' => [
                    'channel'  => $channel,
                    //'projects' => $projects
                ]
            ]
        ];

        elastic()->update($params);
    }

    public static function remove_user_email($project, $uuid, array $email) {
        $params = [
            'index'   => static::$index,
            'type'    => static::$type,
            'id'      => $project . '_' . $uuid,
            'refresh' => true,
            'body'    => [
                'script' => 'if(ctx._source.containsKey("emails")) {ctx._source.emails.removeAll{it == e}}',
                'params' => [
                    'e'    => $email
                ]
            ]
        ];

        return elastic()->update($params);
    }

    public static function set_user_email($project, $uuid, array $field) {
        $params = [
            'index'   => static::$index,
            'type'    => static::$type,
            'id'      => $project . '_' . $uuid,
            'refresh' => true,
            'body'    => [
                /*
                def f = [
                    label: field.label,
                    default: field.default,
                    email: field.email
                ];

                if(ctx._source.containsKey("emails")) {
                    def found = false;

                    ctx._source.emails?.each {
                        obj -> if (obj.email == f.email) {
                            obj = field;
                            found = true
                         }
                    };

                    if (! found) {
                        ctx._source.emails += f;
                    }
                } else {
                    ctx._source.emails = [f];
                }
                 */
                'script' => 'def f = [
                    label: field.label,
                    default: field.default,
                    email: field.email
                ];

                if(ctx._source.containsKey("emails")) {
                    def found = false;

                    ctx._source.emails?.each {
                        obj -> if (obj.email == f.email) {
                            obj = field;
                            found = true
                         }
                    };

                    if (! found) {
                        ctx._source.emails += f;
                    }
                } else {
                    ctx._source.emails = [f];
                }',
                'params' => [
                    'field'  => $field
                ]
            ]
        ];

        return elastic()->update($params);
    }

    public static function create_or_update_user(array $projects, array $channel) {
        if ($channel['type'] == 'facebook') {
            $existing = static::getUsersByFbId($channel['user_id']);

            if ($existing->isEmpty()) {

                if (isset($channel['email'])) {
                    $existing = CRM::getUsersByEmail($channel['email']);

                    if ($existing->isEmpty()) {
                        $uuid = static::generate_uuid();

                        foreach($projects as $project) {
                            static::create_user($project, $uuid, $channel);
                        }
                    }

                    // Controllare se è presente nel CRM dello stesso progetto
                    foreach($projects as $project) {
                        if (static::is_user_in_project($uuid, $project)) {
                            static::add_channel_to_user($project, $uuid, $channel);
                        } else {
                            static::create_user($project, $uuid, $channel);
                        }
                    }
                } else {
                    $uuid = static::generate_uuid();

                    foreach($projects as $project) {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            } else {
                if ($existing->count() > 1) {
                    $uuid = static::merge($existing->pluck('key')->toArray());
                } else {
                    $uuid = $existing[0]['key'];
                }

                // Controllare se è presente nel CRM dello stesso progetto
                foreach($projects as $project) {
                    if (static::is_user_in_project($uuid, $project)) {
                        static::add_channel_to_user($project, $uuid, $channel);
                    } else {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            }

            return $uuid;
        }

        if ($channel['type'] == 'woocommerce') {
            $existing = CRM::getUsersByEmail($channel['email']);

            if ($existing->isEmpty()) {
                $uuid = static::generate_uuid();

                foreach($projects as $project) {
                    static::create_user($project, $uuid, $channel);
                }
            } else {
                if ($existing->count() > 1) {
                    $uuid = static::merge($existing->pluck('key')->toArray());
                } else {
                    $uuid = $existing[0]['key'];
                }

                // Controllare se è presente nel CRM dello stesso progetto
                foreach($projects as $project) {
                    if (static::is_user_in_project($uuid, $project)) {
                        static::add_channel_to_user($project, $uuid, $channel);
                    } else {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            }

            return $uuid;
        }

        if ($channel['type'] == 'crm') {

            $uuids = [];

            foreach($channel['email'] as $email) {
                $existing = CRM::getUsersByEmail($email);

                if ($existing->count() > 1) {
                    $uuids[] = static::merge($existing->pluck('key')->toArray());
                } else if ($existing->count() == 1) {
                    $uuids[] = $existing[0]['key'];
                }
            }

            if (count($uuids) > 1) {
                // MERGE or Error?
                dd('stop');
            }

            if (empty($uuids)) {
                $uuid = static::generate_uuid();

                $channel['user_id'] = $uuid;

                foreach($projects as $project) {
                    static::create_user($project, $uuid, $channel);
                }
            } else {
                if (count($uuids) > 1) {
                    $uuid = static::merge($existing->pluck('key')->toArray());
                    // sicuro merge? Csv con 2 email diverse, che matchano 2 utenti diversi di altri canali
                } else {
                    $uuid = $uuids[0];
                }

                $channel['user_id'] = $uuid;

                // Controllare se è presente nel CRM dello stesso progetto
                foreach($projects as $project) {
                    if (static::is_user_in_project($uuid, $project)) {
                        static::add_channel_to_user($project, $uuid, $channel);
                    } else {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            }

            return $uuid;
        }

        if ($channel['type'] == 'captive') {
            // Right now all captive are FACEBOOK

            $fb_id = explode('/', $channel['user_id']);

            $existing = static::getUsersByFbId($fb_id[1]);

            if ($existing->isEmpty()) {

                if (isset($channel['email'])) {
                    $existing = CRM::getUsersByEmail($channel['email']);

                    if ($existing->isEmpty()) {
                        $uuid = static::generate_uuid();

                        foreach($projects as $project) {
                            static::create_user($project, $uuid, $channel);
                        }
                    }

                    // Controllare se è presente nel CRM dello stesso progetto
                    foreach($projects as $project) {
                        if (static::is_user_in_project($uuid, $project)) {
                            static::add_channel_to_user($project, $uuid, $channel);
                        } else {
                            static::create_user($project, $uuid, $channel);
                        }
                    }
                } else {
                    $uuid = static::generate_uuid();

                    foreach($projects as $project) {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            } else {
                if ($existing->count() > 1) {
                    $uuid = static::merge($existing->pluck('key')->toArray());
                } else {
                    $uuid = $existing[0]['key'];
                }

                // Controllare se è presente nel CRM dello stesso progetto
                foreach($projects as $project) {
                    if (static::is_user_in_project($uuid, $project)) {
                        static::add_channel_to_user($project, $uuid, $channel);
                    } else {
                        static::create_user($project, $uuid, $channel);
                    }
                }
            }

            return $uuid;
        }
    }

    public static function merge(array $uuids) {
        $uuids = array_unique($uuids);

        if (count($uuids) < 2) {
            \Log::info('MERGING ABORTED: same ids.');
            return;
        }

        \Log::info('MERGING');
        \Log::info($uuids);

        $keep = $uuids[0];

        $merging = array_slice($uuids, 1);

        foreach($merging as $uuid) {
            // update all user with uuid to keep
            $params = [
                'index'     => \App\Channels\Facebook\User::$index . ',' . \App\Channels\Woocommerce\Customer::$index,
                'type'      => 'facebook,woocommerce',
                //'conflicts' => 'proceed',
                'body'      => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term'    => ['uuid' => $uuid]],
                            ]
                        ]
                    ],
                    'script' => [
                        'inline' => 'ctx._source.uuid = keep',
                        'params' => [
                            'keep' => $keep
                        ]
                    ]
                ]
            ];

            elastic()->updateByQuery($params);

            // merge crm uuid into keep
            $params = [
                'index' => static::$index,
                'type'  => static::$type,
                'body'  => [
                    'size'  => 100,
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['uuid' => $uuid]]
                            ]
                        ]
                    ]
                ]
            ];

            $crms = elastic()->search($params)['hits']['hits'];

            // TODO: USED POINTS
            foreach($crms as $crm) { // foreach crm project
                try {
                    // get the keep-crm for the SAME project
                    $p_crm = elastic()->get([
                        'index' => static::$index,
                        'type'  => static::$type,
                        'id'    => $crm['_source']['project'] . '_' . $keep,
                    ]);

                    $p_channels = $p_crm['_source']['channels'];


                    if (! isset($p_crm['_source']['custom_fields'])) {
                        $p_crm['_source']['custom_fields'] = [];
                    }

                    $p_custom_fields = $p_crm['_source']['custom_fields'];


                    // TODO: Check if correct
                    if (! isset($crm['_source']['points'])) {
                        $crm['_source']['points'] = 0;
                    }

                    // TODO: Check if correct
                    if (! isset($crm['_source']['unused_points'])) {
                        $crm['_source']['unused_points'] = 0;
                    }

                    $crm['_source']['points']        += (isset($p_crm['_source']['points'])) ? $p_crm['_source']['points'] : 0;
                    $crm['_source']['unused_points'] += (isset($p_crm['_source']['unused_points'])) ? $p_crm['_source']['unused_points'] : 0;


                    foreach($p_channels as $p_channel) {
                        $found = false;

                        foreach($crm['_source']['channels'] as $c_channel) {
                            if ($p_channel['type'] == $c_channel['type'] && $p_channel['user_id'] == $c_channel['user_id']) {
                                $found = true;
                                break;
                            }
                        }

                        if (! $found) {
                            $crm['_source']['channels'][] = $p_channel;
                        }
                    }

                    foreach($p_custom_fields as $p_field) {
                        $found = false;

                        if (isset($crm['_source']['custom_fields'])) {
                            foreach($crm['_source']['custom_fields'] as $c_field) {
                                if ($p_field['category'] == $c_field['category'] && $p_field['name'] == $c_field['name'] && $p_field['type'] == $c_field['type']) {
                                    if ($p_field['val_' . $p_field['type']] == $c_field['val_' . $c_field['type']]) {
                                        $found = true;
                                        break;
                                    } else {
                                        // Se il valore è diverso creoo un nuovo field {name}_merged_{project-id}
                                        $p_field['name'] = $p_field['name'] . '_merged_' . $crm['_source']['project'];
                                    }

                                }
                            }
                        }

                        if (! $found) {
                            $crm['_source']['custom_fields'][] = $p_field;
                        }
                    }
                } catch(Missing404Exception $e) {
                    \Log::info($e->getMessage());
                    \Log::info($e->getTraceAsString());
                    // nothing
                }

                // Add to CRM KEEP the merged channels, custom_fields & points
                $params = [
                    'index' => static::$index,
                    'type'  => static::$type,
                    'id'    => $crm['_source']['project'] . '_' . $keep,
                    'body'  => [
                        'uuid'          => $keep,
                        'project'       => $crm['_source']['project'],
                        'channels'      => $crm['_source']['channels'],
                        'points'        => $crm['_source']['points'],
                        'unused_points' => $crm['_source']['unused_points'],
                    ]
                ];

                if (isset($crm['_source']['custom_fields'])) {
                    $params['body']['custom_fields'] = $crm['_source']['custom_fields'];
                }

                elastic()->index($params);


                // delete crm uuid (because everything was merged inside the KEEP-CRM)
                elastic()->delete([
                    'index' => static::$index,
                    'type'  => static::$type,
                    'id'    => $crm['_id'],
                ]);
            }
        }

        return $keep;
    }

    public static function sync_user_points(array $projects, $uuid) {
        $params = [
            'size' => 100,
            'body' => [
                '_source' => ['points'],
                'query' => [
                    'bool' => [
                        'must_not' => [
                            ['term' => ['_index' => 'crm']],
                        ],
                        'must' => [
                            ['term' => ['uuid' => $uuid]],
                            ['terms' => ['projects' => $projects]],
                        ]
                    ]
                ]
            ]
        ];

        $points = collect(elastic()->search($params)['hits']['hits'])->sum('_source.points');

        foreach($projects as $project) {

            $used_points = elastic()->search([
                'index'  => static::$index,
                'type'   => 'used_points',
                'body' => [
                    'size' => 0,
                    'query' => [
                        'term' => [
                            '_parent' => $project . '_' . $uuid
                        ]
                    ],
                    'aggs' => [
                        'used' => [
                            'sum' => [
                                'field' => 'amount'
                            ]
                        ]
                    ]
                ]
            ])['aggregations']['used']['value'];


            $params = [
                'index'   => static::$index,
                'type'    => static::$type,
                'id'      => $project . '_' . $uuid,
                'body'    => [
                    'doc' => [
                        'points' => $points,
                        'unused_points' => ($points - $used_points)
                    ]
                ]
            ];

            elastic()->update($params);
        }
    }

    public static function change_user_points(array $projects, $uuid, array $channel, $points) {
        if ($points == 0) {
            return;
        }

        if ($points > 0) {
            $script = 'ctx._source.points +=' . $points . '; ctx._source.unused_points +=' . $points;
        } else {
            $script = 'ctx._source.points -=' . abs($points) . '; ctx._source.unused_points -=' . abs($points);
        }

        foreach($projects as $project) {
            $params = [
                'index'   => static::$index,
                'type'    => static::$type,
                'id'      => $project . '_' . $uuid,
                //'refresh' => true,
                'body'    => [
                    'script' => $script,
                ]
            ];

            elastic()->update($params);
        }
    }

    public static function getUsersByFbId($id) {
        $elastic = app()->make(\Elasticsearch\Client::class);

        $params = [
            'index' => \App\Channels\Facebook\User::$index,
            'type'  => 'facebook',
            'body' => [
                'size'  => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['user_id' => $id]]
                        ]
                    ]
                ],
                'aggs' => [
                    'uuid' => [
                        'terms' => [
                            'size'  => 0,
                            'field' => 'uuid'
                        ]
                    ]
                ]
            ]
        ];

        return collect($elastic->search($params)['aggregations']['uuid']['buckets']);
    }

    public static function getUsersByEmail($email) {
        $elastic = app()->make(\Elasticsearch\Client::class);

        // $params = [
        //     'index' => \App\Channels\Facebook\User::$index . ',' . \App\Channels\Woocommerce\Customer::$index,
        //     'type'  => 'facebook,woocommerce,crm',
        //     'body' => [
        //         'size'  => 0,
        //         'query' => [
        //             'bool' => [
        //                 'minimum_should_match' => 1,
        //                 'should' => [
        //                     ['match_phrase' => ['email' => $email]],
        //                     [
        //                         'nested' => [
        //                             'path' => 'channels',
        //                             'query' => [
        //                                 'term' => ['email.raw' => $email]
        //                             ]
        //                         ]
        //                     ]
        //                 ]
        //             ]
        //         ],
        //         'aggs' => [
        //             'uuid' => [
        //                 'terms' => [
        //                     'size'  => 0,
        //                     'field' => 'uuid'
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];

        $params = [
            'index' => static::$index,
            'type'  => static::$type,
            'body' => [
                'size'  => 0,
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'should' => [
                            ['match_phrase' => ['email' => $email]],
                            ['term' => ['email.raw' => $email]],
                            [
                                'nested' => [
                                    'path' => 'channels',
                                    'query' => [
                                        'term' => ['channels.email.raw' => $email]
                                    ]
                                ]
                            ],
                            [
                                'nested' => [
                                    'path' => 'emails',
                                    'query' => [
                                        'term' => ['emails.email.raw' => $email]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'aggs' => [
                    'uuid' => [
                        'terms' => [
                            'size'  => 0,
                            'field' => 'uuid'
                        ]
                    ]
                ]
            ]
        ];

        $from_crm = collect($elastic->search($params)['aggregations']['uuid']['buckets'])->keyBy('key');

        $params = [
            'index' => implode(',', [\App\Channels\Facebook\User::$index, \App\Channels\Woocommerce\Customer::$index, \App\LoyaltyMember::$index]),
            'type'  => 'facebook,woocommerce,users,members',
            'body' => [
                'size'  => 0,
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'should' => [
                            ['match_phrase' => ['email' => $email]],
                            ['term' => ['email.raw' => $email]],
                        ]
                    ]
                ],
                'aggs' => [
                    'uuid' => [
                        'terms' => [
                            'size'  => 0,
                            'field' => 'uuid'
                        ]
                    ]
                ]
            ]
        ];

        $from_channels = collect($elastic->search($params)['aggregations']['uuid']['buckets'])->keyBy('key');

        // TODO: probably should sum values
        return $from_crm->merge($from_channels)->values();
    }

    public static function getUsersByPhone($phone) {
        $params = [
            'index' => implode(',', [\App\Channels\Woocommerce\Customer::$index, \App\LoyaltyMember::$index]),
            'type'  => 'woocommerce,members',
            'body' => [
                'size'  => 0,
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'should' => [
                            ['term' => ['billing_address_raw.phone' => $phone]],
                            ['term' => ['billing_address_raw.phone' => str_replace('+39', '', $phone)]],
                            ['term' => ['phone' => $phone]],
                        ]
                    ]
                ],
                'aggs' => [
                    'uuid' => [
                        'terms' => [
                            'size'  => 0,
                            'field' => 'uuid'
                        ]
                    ]
                ]
            ]
        ];

        return collect(elastic()->search($params)['aggregations']['uuid']['buckets']);
    }

    public static function set_user_custom_field($project, $uuid, array $field) {
        $params = [
            'index'   => static::$index,
            'type'    => static::$type,
            'id'      => $project . '_' . $uuid,
            'refresh' => true,
            'body'    => [
                /*
                def f = [
                    name: field.name,
                    category: field.category,
                    type: field.type
                ];

                f["val_" + field.type] = field.value;

                if(ctx._source.containsKey("custom_fields")) {
                    def found = false;

                    ctx._source.custom_fields?.each {
                        obj -> if (obj.name == f.name && obj.category == f.category) {
                            obj["val_" + field.type] = field.value;
                            found = true
                         }
                    };

                    if (! found) {
                        ctx._source.custom_fields += f;
                    }
                } else {
                    ctx._source.custom_fields = [f];
                }
                 */
                'script' => 'def f = [name: field.name, category: field.category, type: field.type]; f["val_" + field.type] = field.value; if(ctx._source.containsKey("custom_fields")) { def found = false; ctx._source.custom_fields?.each { obj -> if (obj.name == f.name && obj.category == f.category) { obj["val_" + field.type] = field.value; found = true } }; if (! found) { ctx._source.custom_fields += f; } } else { ctx._source.custom_fields = [f]; }',
                'params' => [
                    'field'  => $field
                ]
            ]
        ];

        return elastic()->update($params);
    }

    public static function set_user_address($project, $uuid, array $address) {
        $params = [
            'index'   => static::$index,
            'type'    => static::$type,
            'id'      => $project . '_' . $uuid,
            'refresh' => true,
            'body'    => [
                'script' => 'if(ctx._source.containsKey("addresses")) {
                    if (ctx._source.addresses.empty) {
                        ctx._source.addresses = [a];
                    } else {
                        def found = false;

                        ctx._source.addresses?.each {
                            obj -> if (obj == a) {
                                found = true;
                            }
                        };

                        if (! found) {
                            ctx._source.addresses += a;
                        }
                    }
                } else {
                    ctx._source.addresses = [a];
                }',
                'params' => [
                    'a'  => $address
                ]
            ]
        ];

        return elastic()->update($params);
    }

    public static function remove_user_address($project, $uuid, $type, $label, array $address) {
        if ($type != 'crm') {
            throw new \Exception('Removing channel\'s address is not supported.');
        }

        $params = [
            'index'   => static::$index,
            'type'    => static::$type,
            'id'      => $project . '_' . $uuid,
            'refresh' => true,
            'body'    => [
                'script' => 'if(ctx._source.containsKey("addresses")) {ctx._source.addresses.removeAll{it == address}}',
                'params' => [
                    'type'    => $type,
                    'label'   => $label,
                    'address' => $address,
                ]
            ]
        ];

        return elastic()->update($params);
    }
}