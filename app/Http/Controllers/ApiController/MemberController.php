<?php

namespace App\Http\Controllers\ApiController;

use App\Loyalty;
use App\Project;
use App\Channel;
use App\LoyaltyMember;
use App\CRM;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return response()->json([
            'error' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function _return_user(array $search) {
        if ($search['total'] == 0) {
            return response()->json([
                'error' => 'Member not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($search['total'] > 1) {
            return response()->json([
                'error' => 'Multiple members found',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        $user = $search['hits'][0]['_source'];

        $fakeid = app()->make('fakeid');

        $member = [
            'uuid'       => $user['uuid'],
            'first_name' => name_case($user['first_name']),
            'last_name'  => name_case($user['last_name']),
            'projects'   => collect($user['projects'])->pluck('id')->unique()->map(function($id) use ($fakeid) {
                //return Project::find($id)->owners->pluck('company_info')->filter()->first();
                return $fakeid->encode($id);
            }),
        ];

        if (\Auth::user()->isTeia()) {
            if (isset($user['email'])) {
                $member['email'] = strtolower($user['email']);
            }

            if (isset($user['phone'])) {
                $member['phone'] = $user['phone'];
            }

            if (isset($user['qr'])) {
                $c = new \Illuminate\Encryption\Encrypter(base64_decode('h8SMiUtvbyNqQemYOfkjPiMx82/39fnBGjsXxbBGXfY='), 'AES-256-CBC');

                $member['pin'] = $c->encrypt(decrypt($user['qr']));
            }
        }

        return response()->json([
            'data' => [
                'member' => $member
            ],
        ], Response::HTTP_OK);
    }

    protected function _search(Request $request) {
        if ($request->has('email')) {
            $term = ['email.raw' => strtolower($request->email)];
        } else if ($request->has('uuid')) {
            $term = ['uuid' => $request->uuid];
        } else {
            $term = ['phone' => $request->phone];
        }

        return elastic()->search([
            'index' => LoyaltyMember::$index,
            'type' => LoyaltyMember::$type,
            'body' => [
                'query' => [
                    'term' => $term
                ]
            ]
        ])['hits'];
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/loyalties/members/verify_pin",
     *   description="Verify PIN and return member with his projects",
     *   summary="Verify PIN and return member with his projects",
     *   operationId="loyalties.members.verify_pin",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member PIN",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_pin")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="multiple members found, something is wrong"),
     *   @SWG\Response(response=404, description="pin not valid, member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_pin",
     *   type="object",
     *   description="Loyalty Member PIN",
     *   required={"pin"},
     *
     *   @SWG\Property(
     *          property="pin",
     *          description="PIN",
     *          type="string",
     *          minLength=6
     *   )
     * )
     */
    public function verify_pin(Request $request) {
        $this->validate($request, [
            'pin' => 'required|alpha_num|min:6'
        ]);

        $hash = hash('sha256', $request->pin);

        $users = elastic()->search([
            'index' => LoyaltyMember::$index,
            'type' => LoyaltyMember::$type,
            'body' => [
                'query' => [
                    'term' => ['pin' => $hash]
                ]
            ]
        ])['hits'];

        return $this->_return_user($users);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/loyalties/members/search",
     *   description="Search Member by uuid, email or phone number return it with his projects",
     *   summary="Search Member by uuid, email or phone number return it with his projects",
     *   operationId="loyalties.members.search",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member Search",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member_search")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="multiple users found, something is wrong"),
     *   @SWG\Response(response=404, description="member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_member_search",
     *   type="object",
     *   description="Loyalty Member Search",
     *
     *   @SWG\Property(
     *          property="uuid",
     *          description="UUID",
     *          type="string",
     *          format="uuid"
     *   ),
     *   @SWG\Property(
     *          property="email",
     *          description="Email",
     *          type="string",
     *          format="email"
     *   ),
     *   @SWG\Property(
     *          property="phone",
     *          description="Phone",
     *          type="string",
     *          example="+3900000000"
     *   )
     * )
     */
    public function search(Request $request) {
        $this->validate($request, [
            'uuid'  => 'required_without_all:phone,email|missing_with:email,phone|uuid',
            'email' => 'required_without_all:uuid,phone|missing_with:uuid,phone|email',
            'phone' => 'required_without_all:uuid,email|missing_with:uuid,email|min:6|regex:/^\+\d+$/',
        ]);

        $users = $this->_search($request);

        return $this->_return_user($users);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/project/{project_id}/loyalties/members/register",
     *   description="Send registration mail/sms to new member",
     *   summary="Send registration mail/sms to new member",
     *   operationId="loyalties.members.register_notify",
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="New Loyalty Member",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member_search")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="member already exists"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function register_notify(Request $request, Project $project) {
        $this->validate($request, [
            'email' => 'required_without:phone|missing_with:phone|email',
            'phone' => 'required_without:email|missing_with:email|min:6|regex:/^\+\d+$/',
        ]);

        $search = $this->_search($request);

        if ($search['total'] > 0) {
            return response()->json([
                'error' => 'Member already exists',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->has('email')) {
            config(['app.name' => 'Loyalty Program']);

            $member = new LoyaltyMember(['email' => strtolower($request->email)]);
        } else {
            $member = new LoyaltyMember(['phone' => $request->phone]);
        }

        try {
            $member->notify(new \App\Notifications\Loyalty\Registration($project));
        } catch(\Exception $e) {
            return response()->json([
                'error' => 'Can\'t send',
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }

        return response()->json([
            'data' => [
                'message' => 'ok'
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Put(
     *   path="/api/v1/project/{project_id}/loyalties/members/subscribe",
     *   description="Send subscription mail/sms to an existing member",
     *   summary="Send subscription mail/sms to an existing member",
     *   operationId="loyalties.members.subscribe_notify",
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member_search")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="already subscribed"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="multiple members found, something is wrong"),
     *   @SWG\Response(response=404, description="member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function subscribe_notify(Request $request, Project $project) {
        $this->validate($request, [
            'uuid'  => 'required_without_all:phone,email|missing_with:email,phone|uuid',
            'email' => 'required_without_all:uuid,phone|missing_with:uuid,phone|email',
            'phone' => 'required_without_all:uuid,email|missing_with:uuid,email|min:6|regex:/^\+\d+$/',
        ]);

        $search = $this->_search($request);

        if ($search['total'] == 0) {
            return response()->json([
                'error' => 'Member not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($search['total'] > 1) {
            return response()->json([
                'error' => 'Multiple members found',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        if (collect($search['hits'][0]['_source']['projects'])->keyBy('id')->has($project->id)) {
            return response()->json([
                'error' => 'Already subscribed',
            ], Response::HTTP_BAD_REQUEST); // 400
        }

        $uuid = $search['hits'][0]['_source']['uuid'];

        config(['app.name' => 'Loyalty Program']);

        $member = new LoyaltyMember($search['hits'][0]['_source']);

        try {
            $member->notify(new \App\Notifications\Loyalty\Subscription($project));
        } catch(\Exception $e) {
            return response()->json([
                'error' => 'Can\'t send',
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }

        return response()->json([
            'data' => [
                'uuid' => $uuid
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Put(
     *   path="/api/v1/loyalties/members/{uuid}/projects",
     *   description="Subscribe an existing member to a project loyalties",
     *   summary="Subscribe an existing member to a project loyalties",
     *   operationId="loyalties.members.subscribe",
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *          name="uuid",
     *          description="UUID of a member",
     *          required=true,
     *          type="string",
     *          format="uuid",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Project ID",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/project_id")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="already subscribed"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project or member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="project_id",
     *   type="object",
     *   description="Project ID",
     *   required={"project"},
     *
     *   @SWG\Property(
     *          property="project",
     *          description="Project id",
     *          type="integer"
     *   )
     * )
     */
    public function subscribe(Request $request, $uuid) {
        $this->validate($request, [
            'project' => 'required|numeric',
        ]);

        $fakeid = app()->make('fakeid');

        $project = Project::find($fakeid->decode($request->project));

        if (! $project) {
            return response()->json([
                'error' => 'Project not found',
            ], Response::HTTP_NOT_FOUND);
        }


        try {
            $member = new LoyaltyMember(elastic()->get(['index' => LoyaltyMember::$index, 'type' => LoyaltyMember::$type, 'id' => $uuid])['_source']);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return response()->json([
                'error' => 'Member not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if (collect($member->projects)->keyBy('id')->has($project->id)) {
            return response()->json([
                'error' => 'Already subscribed',
            ], Response::HTTP_BAD_REQUEST); // 400
        }

        $member->subscribe($project);

        $loyalty = [
            'type'       => 'loyalty',
            'channel_id' => $project->id,
            'user_id'    => $uuid,
            'user_name'  => $member->first_name . ' ' . $member->last_name,
            'email'      => strtolower($member->email),
            'phone'      => $member->phone,
        ];

        if (CRM::is_user_in_project($uuid, $project->id)) {
            // TODO: verificare se channel loyalty esiste già in crm (forse non può mai esistere già)
            CRM::add_channel_to_user($project->id, $uuid, $loyalty);
        } else {
            CRM::create_user($project->id, $uuid, $loyalty);
        }

        return response()->json([
            'data' => [
                'message' => 'ok'
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/loyalties/members/create",
     *   description="Create a loyalty member in ES",
     *   summary="Create a loyalty member in ES",
     *   operationId="loyalties.members.create",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="multiple crm users found, something is wrong"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_member",
     *   type="object",
     *   description="Loyalty Member",
     *
     *   @SWG\Property(
     *          property="first_name",
     *          description="First Name",
     *          type="string"
     *   ),
     *   @SWG\Property(
     *          property="last_name",
     *          description="Last Name",
     *          type="string"
     *   ),
     *   @SWG\Property(
     *          property="email",
     *          description="Email",
     *          type="string",
     *          format="email"
     *   ),
     *   @SWG\Property(
     *          property="phone",
     *          description="Phone",
     *          type="string",
     *          example="+3900000000"
     *   ),
     *   @SWG\Property(
     *          property="password",
     *          description="Password",
     *          type="string"
     *   )
     * )
     */
    public function create(Request $request) {
        $this->validate($request, [
            'first_name' => 'required|regex:/^[\' \p{L}\d]+$/u',
            'last_name'  => 'required|regex:/^[\' \p{L}\d]+$/u',
            'email'      => 'required|email',
            'phone'      => 'required|min:6|regex:/^\+\d+$/',
            'password'   => 'required|min:6'
        ]);

        $users = elastic()->search([
            'index' => LoyaltyMember::$index,
            'type' => LoyaltyMember::$type,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            ['term' => ['email.raw' => strtolower($request->email)]],
                            ['term' => ['phone' => $request->phone]],
                        ]
                    ]
                ]
            ]
        ])['hits'];

        if ($users['total'] > 0) {
            return response()->json([
                'error' => 'Member already exists',
            ], Response::HTTP_BAD_REQUEST); // 400
        }

        $crm_emails = CRM::getUsersByEmail(strtolower($request->email));

        if (count($crm_emails) > 1) {
            return response()->json([
                'error' => 'Multiple crm users found',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        $uuids = [];

        if (count($crm_emails) == 1) {
            $uuids[] = $crm_emails[0]['key'];
        }

        $crm_phones = CRM::getUsersByPhone($request->phone);

        if (count($crm_phones) > 1) {
            return response()->json([
                'error' => 'Multiple crm users found',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        if (count($crm_phones) == 1) {
            $uuids[] = $crm_phones[0]['key'];
        }

        $uuids = array_unique($uuids);

        if (empty($uuids)) {
            $uuids[] = CRM::generate_uuid();
        }

        if (count($uuids) > 1) {
            return response()->json([
                'error' => 'TODO: MERGE USER', // TODO: merge
            ], Response::HTTP_FORBIDDEN); // 403
        }

        $uuid = $uuids[0];

        // return elastic()->delete([
        //     'id'    => '3105f9ae-af30-4bfa-b678-f90963833ec2',
        //     'index' => LoyaltyMember::$index,
        //     'type'  => 'members',
        // ]);

        $pin = LoyaltyMember::generate_pin();

        LoyaltyMember::create([
            'uuid'       => $uuid,
            'pin'        => hash('sha256', $pin),
            'qr'         => encrypt($pin),
            'password'   => \Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'email'      => strtolower($request->email),
            'created_at' => gmdate(DATE_ISO8601, time()),
        ]);

        return response()->json([
            'data' => [
                'member' => [
                    'uuid' => $uuid,
                    'pin'  => $pin,
                ]
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Put(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/leaderboard/member/{uuid}/address",
     *   description="Save loyalty member address in CRM",
     *   operationId="loyalty.leaderboard.member.address",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="loyalty_id",
     *          description="ID of the loyalty program",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="uuid",
     *          description="UUID of a member",
     *          required=true,
     *          type="string",
     *          format="uuid",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member Address",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member_address")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project/loyalty/member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_member_address",
     *   type="object",
     *   description="Member Address",
     *   required={"address"},
     *
     *   @SWG\Property(
     *          property="address",
     *          description="Address",
     *          type="string",
     *   )
     * )
     */
    public function save_address(Request $request, Project $project, Loyalty $loyalty, $uuid) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->validate($request, [
            'address' => 'required|min:8',
        ]);

        try {
            $member = elastic()->get([
                'index' => LoyaltyMember::$index,
                'type'  => LoyaltyMember::$type,
                'id'    => $uuid,
            ])['_source'];

            $crm = elastic()->get([
                'index' => CRM::$index,
                'type'  => CRM::$type,
                'id'    => $project->id . '_' . $uuid,
            ])['_source'];

            if (isset($crm['addresses'])) {
                $addresses = collect($crm['addresses'])->where('type', 'crm')->where('label', '_loyalty');
            } else{
                $addresses = collect([]);
            }

        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return response()->json([
                'error' => 'Member not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $projects = collect($member['projects'])->keyBy('id');

        if (! isset($projects[$project->id])) {
            return response()->json([
                'error' => 'Member not subscribed to this loyalty.',
            ], Response::HTTP_FORBIDDEN);
        }

        $address = geocode($request->address);

        if ($address == null){
            return response()->json([
                'error' => [
                    'address' => [
                        'The address is not valid.'
                    ]
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $address['type'] = 'crm';
        $address['label'] = '_loyalty';

        if (! empty($addresses)) {
            foreach($addresses as $remove) {
                CRM::remove_user_address($project->id, $uuid, 'crm', '_loyalty', $remove);
            }
        }

        CRM::set_user_address($project->id, $uuid, $address);

        return response()->json([
            'data' => [
                'address' => $address['formatted']
            ],
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Put(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/leaderboard/member/{uuid}/note",
     *   description="Save loyalty member note in CRM",
     *   operationId="loyalty.leaderboard.member.note",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="loyalty_id",
     *          description="ID of the loyalty program",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="uuid",
     *          description="UUID of a member",
     *          required=true,
     *          type="string",
     *          format="uuid",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Member Note",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_member_note")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project/loyalty/member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_member_note",
     *   type="object",
     *   description="Member Note",
     *   required={"note"},
     *
     *   @SWG\Property(
     *          property="note",
     *          description="note",
     *          type="string",
     *   )
     * )
     */
    public function save_note(Request $request, Project $project, Loyalty $loyalty, $uuid) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->validate($request, [
            'note' => 'required|min:1',
        ]);

        try {
            $member = elastic()->get([
                'index' => LoyaltyMember::$index,
                'type'  => LoyaltyMember::$type,
                'id'    => $uuid,
            ])['_source'];
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return response()->json([
                'error' => 'Member not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $projects = collect($member['projects'])->keyBy('id');

        if (! isset($projects[$project->id])) {
            return response()->json([
                'error' => 'Member not subscribed to this loyalty.',
            ], Response::HTTP_FORBIDDEN);
        }

        CRM::set_user_custom_field($project->id, $uuid, [
            'category' => 'Loyalty',
            'name'     => 'Note',
            'type'     => 'text',
            'value'    => $request->note,
        ]);

        return response()->json([
            'data' => [
                'message' => 'ok'
            ],
        ], Response::HTTP_OK);
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required_without:phone|missing_with:phone|email',
            'phone' => 'required_without:email|missing_with:email|min:6|regex:/^\+\d+$/',
        ]);

        $users = $this->_search($request);

        if ($users['total'] == 0) {
            return response()->json([
                'error' => 'Member not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($users['total'] > 1) {
            return response()->json([
                'error' => 'Multiple members found',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        $member = $users['hits'][0]['_source'];

        if (\Hash::check(strtolower($request->password), $member['password'])) {
            return $this->_return_user($users);
        } else {
            return response()->json([
                'error' => 'Wrong credential',
            ], Response::HTTP_FORBIDDEN); // 403
        }
    }

    public function project_with_merchant(Request $request, $uuid) {
        try {
            $member = elastic()->get([
                'index' => LoyaltyMember::$index,
                'type'  => LoyaltyMember::$type,
                'id'    => $uuid,
            ])['_source'];
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return response()->json([
                'error' => 'Member not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $subscriptions = collect($member['projects'])->keyBy('id');

        $projects = Project::with('owners')->find($subscriptions->keys())->keyBy('id');

        /*
        {
    "1": {
        "id": 1,
        "name": "Prova",
        "created_at": "2018-04-20 13:40:48",
        "updated_at": "2018-04-20 13:40:48",
        "owners": [
            {
                "id": 1,
                "name": "Stefano Colao",
                "email": "stefano.colao@teia.company",
                "fb_id": null,
                "fb_email": null,
                "created_at": "2018-04-20 13:35:20",
                "updated_at": "2018-04-20 13:35:20",
                "verified": 1,
                "verification_token": null,
                "stripe_id": null,
                "card_brand": null,
                "card_last_four": null,
                "trial_ends_at": null,
                "company_info": "TEIA",
                "address": null,
                "vat_number": null,
                "sale_points": null,
                "company_phone": null,
                "company_address": null,
                "note": null,
                "phone_number": null,
                "company": null,
                "skip_tutorial": 0,
                "poynt_response": null,
                "self_signed_token": null,
                "poynt_response_token": null,
                "device_id": null,
                "business_id": null,
                "pivot": {
                    "project_id": 1,
                    "user_id": 1
                }
            }
        ]
    },
    "3": {
        "id": 3,
        "name": "Default Poynt Project",
        "created_at": "2018-05-23 11:39:18",
        "updated_at": "2018-05-23 11:39:18",
        "owners": [
            {
                "id": 1,
                "name": "Stefano Colao",
                "email": "stefano.colao@teia.company",
                "fb_id": null,
                "fb_email": null,
                "created_at": "2018-04-20 13:35:20",
                "updated_at": "2018-04-20 13:35:20",
                "verified": 1,
                "verification_token": null,
                "stripe_id": null,
                "card_brand": null,
                "card_last_four": null,
                "trial_ends_at": null,
                "company_info": "TEIA",
                "address": null,
                "vat_number": null,
                "sale_points": null,
                "company_phone": null,
                "company_address": null,
                "note": null,
                "phone_number": null,
                "company": null,
                "skip_tutorial": 0,
                "poynt_response": null,
                "self_signed_token": null,
                "poynt_response_token": null,
                "device_id": null,
                "business_id": null,
                "pivot": {
                    "project_id": 3,
                    "user_id": 1
                }
            }
        ]
    }
}
         */
        return $subscriptions->map(function($s) use ($projects) {
            if (! isset($projects[$s['id']])) {
                return false;
            }

            $project = $projects[$s['id']];

            $s['id'] = $project->getRouteKey();
            $s['name'] = $project->name;

            $s['merchant'] = [
                'name'            => $s['name'],
                'company'         => $project->owners[0]->company,
                'company_info'    => $project->owners[0]->company_info,
                'address'         => $project->owners[0]->address,
                'phone'           => $project->owners[0]->phone,
                'company_address' => $project->owners[0]->company_address,
                'company_phone'   => $project->owners[0]->company_phone,
                'vat_number'      => $project->owners[0]->vat_number,
            ];

            return $s;
        })->filter()->values();
    }
}