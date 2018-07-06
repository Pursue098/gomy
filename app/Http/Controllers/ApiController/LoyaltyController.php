<?php

namespace App\Http\Controllers\ApiController;

use App\Loyalty;
use App\Project;
use App\Channel;
use App\LoyaltyMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class LoyaltyController extends Controller
{
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return response()->json([
            'error' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/project/{project_id}/loyalties",
     *   description="List all Loyalty Program of a project",
     *   summary="List all Loyalty Program of a project",
     *   operationId="loyalty.index",
     *
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function index(Request $request, Project $project) {
        $loyalties = collect($project->loyalties()->with('channels')->get())->map(function($loyalty) {
            $channels = $loyalty->channels->map(function($channel) {
                $channel->settings = json_decode($channel->pivot->settings);
                unset($channel->pivot);
                unset($channel->channable);
                return $channel->toApi();
            });

            unset($loyalty->channels);

            $loyalty->channels = $channels;
            return $loyalty->toApi();
        });

        return response()->json([
            'data' => [
                'loyalties' => $loyalties
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/project/{project_id}/loyalties",
     *   description="Create a new Loyalty Program and return it",
     *   summary="Create a new Loyalty Program and return it",
     *   operationId="loyalty.create",
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Loyalty Program",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty",
     *   type="object",
     *   description="Loyalty Program",
     *   required={"name","start_at"},
     *
     *   @SWG\Property(
     *          property="name",
     *          description="Loyalty Program name",
     *          type="string",
     *   ),
     *   @SWG\Property(
     *          property="start_at",
     *          description="Initial date of the Loyalty Program",
     *          type="string",
     *          format="date",
     *          example="2018-05-23"
     *   )
     * )
     */
    public function create(Request $request, Project $project) {
        $this->validate($request, [
            'name'     => 'required|string|max:100',
            'start_at' => 'required|date|after_or_equal:today',
        ]);

        $loyalty = $project->loyalties()->create([
            'name'     => $request->name,
            'start_at' => $request->start_at,
        ]);

        return response()->json([
            'data' => [
                'loyalty' => $loyalty->toApi()
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Put(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}",
     *   description="Update an existing Loyalty Program",
     *   summary="Update an existing Loyalty Program",
     *   operationId="loyalty.update",
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
     *          name="loyalty_id",
     *          description="ID of the loyalty program",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_update")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_update",
     *   type="object",
     *   description="Loyalty Program",
     *
     *   @SWG\Property(
     *          property="name",
     *          description="Loyalty Program name",
     *          type="string",
     *   ),
     *   @SWG\Property(
     *          property="status",
     *          description="Status of the loyalty program",
     *          type="string",
     *          enum={"active","suspended","terminated"}
     *   )
     * )
     */
    public function update(Request $request, Project $project, Loyalty $loyalty) {
        $this->validate($request, [
            'name'   => 'required_without:status|string|max:100',
            'status' => 'required_without:name|alpha|in:active,suspended,terminated',
        ]);

        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $loyalty->name = $request->get('name', $loyalty->name);
        $loyalty->status = $request->get('status', $loyalty->status);

        $loyalty->save();

        return response()->json([
            'data' => [
                'loyalty' => $loyalty->toApi()
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/channels/poynts",
     *   description="Add a Poynt channel configuration to the specified Loyalty Program",
     *   summary="Add a Poynt channel configuration to the specified Loyalty Program",
     *   operationId="loyalty.channels.add",
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
     *          name="loyalty_id",
     *          description="ID of the loyalty program",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          description="Poynt Channel Confgiguration",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/loyalty_poynt_config")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="channel already associated"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="loyalty_poynt_config",
     *   type="object",
     *   description="Poynt Channel Confgiguration",
     *   required={"channel_id"},
     *
     *   @SWG\Property(
     *       property="channel_id",
     *       type="integer",
     *       description="ID of the Poynt channel",
     *   ),
     *   @SWG\Property(
     *       property="exact",
     *       type="array",
     *       description="Exact match: amount -> points",
     *       @SWG\Items(
     *           type="object",
     *           required={"amount","points"},
     *           @SWG\Property(
     *               property="amount",
     *               type="integer"
     *           ),
     *           @SWG\Property(
     *               property="points",
     *               type="integer"
     *           )
     *       )
     *   ),
     *   @SWG\Property(
     *       property="range",
     *       type="array",
     *       description="Range march: from-to -> points",
     *       @SWG\Items(
     *           type="object",
     *           required={"from","to","points"},
     *           @SWG\Property(
     *               property="from",
     *               type="integer"
     *           ),
     *               @SWG\Property(
     *               property="to",
     *               type="integer"
     *           ),
     *               @SWG\Property(
     *               property="points",
     *               type="integer"
     *           )
     *       )
     *   ),
     *   @SWG\Property(
     *       property="items",
     *       type="array",
     *       description="Items march: amount -> points",
     *       @SWG\Items(
     *           type="object",
     *           required={"amount","points"},
     *           @SWG\Property(
     *               property="amount",
     *               type="integer"
     *           ),
     *           @SWG\Property(
     *               property="points",
     *               type="integer"
     *           )
     *       )
     *   )
     * )
     */
    public function add_poynt_channel(Request $request, Project $project, Loyalty $loyalty) {
        $this->validate($request, [
            'channel_id'     => 'required|integer',
            'exact'          => 'array|required_without_all:range,items',
            'range'          => 'array|required_without_all:exact,items',
            'items'          => 'array|required_without_all:exact,range',
            'exact.*.amount' => 'required|numeric|min:0',
            'exact.*.points' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.points' => 'required|numeric|min:0',
            'range.*.from'   => 'required|numeric',
            'range.*.to'     => 'required|numeric',
            'range.*.points' => 'required|numeric|min:0',
        ]);

        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $channel_id = app()->make('fakeid')->decode($request->channel_id);

        $channel = $project->channels->keyBy('id')->get($channel_id);

        if (! $channel) {
            return response()->json([
                'error' => 'Channel not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($channel->type != 'poynt' || $channel->status != 'assigned' || $channel->channable->status != 'grabbed') {
            return response()->json([
                'error' => 'Channel not valid',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($loyalty->channels->keyBy('id')->has($channel->id)) {
            return response()->json([
                'error' => 'Channel already associated',
            ], Response::HTTP_FORBIDDEN);
        }

        $config = collect($request->only('exact', 'range', 'items'))->filter();

        $loyalty->channels()->save($channel, ['settings' => $config->toJson()]);

        return response()->json([
            'data' => [
                'loyalty' => $loyalty->toApi(),
                'channel' => [
                    'id'     => $request->channel_id,
                    'type'   => 'poynt',
                    'config' => $config,
                ]
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/dashboard/metrics",
     *   description="Retrieve loyalty dashboard metrics",
     *   operationId="loyalty.dashboard.metrics",
     *
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
     *   @SWG\Response(
     *       response=200,
     *       description="successful operation",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(
     *               property="points",
     *               type="object",
     *               @SWG\Property(
     *                   property="total",
     *                   type="integer",
     *                   example=100
     *               ),
     *               @SWG\Property(
     *                   property="used",
     *                   type="integer",
     *                   example=20
     *               ),
     *               @SWG\Property(
     *                   property="unused",
     *                   type="integer",
     *                   example=80
     *               )
     *           ),
     *           @SWG\Property(
     *               property="channels",
     *               type="integer",
     *               example=1
     *           ),
     *           @SWG\Property(
     *               property="users",
     *               type="integer",
     *               example=100
     *           ),
     *           @SWG\Property(
     *               property="loyalties",
     *               type="integer",
     *               example=3
     *           )
     *       )
     *   ),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function dashboard_metrics(Request $request, Project $project, Loyalty $loyalty) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return [
            'points' => [
                'total'  => 100, // TODO: mock
                'used'   => 20, // TODO: mock
                'unused' => 80, // TODO: mock
            ],
            'channels'  => $loyalty->channels->count(),
            'users'     => 1, // TODO: mock
            'loyalties' => $project->loyalties->where('status', 'active')->where('start_at', '<', date('Y-m-d H:i:s'))->count(),
        ];
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/dashboard/tops",
     *   description="Retrieve loyalty dashboard top members",
     *   operationId="loyalty.dashboard.tops",
     *
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
     *   @SWG\Response(
     *       response=200,
     *       description="successful operation",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(
     *               property="by_points",
     *               type="object",
     *               @SWG\Property(
     *                   property="uuid",
     *                   type="string",
     *                   example="dc640254-9745-4c52-be99-b44fbe0029c7"
     *               ),
     *               @SWG\Property(
     *                   property="first_name",
     *                   type="string",
     *                   example="Test"
     *               ),
     *               @SWG\Property(
     *                   property="last_name",
     *                   type="integer",
     *                   example="User"
     *               ),
     *               @SWG\Property(
     *                   property="points",
     *                   type="integer",
     *                   example=100
     *               ),
     *               @SWG\Property(
     *                   property="channels",
     *                   type="object",
     *                   @SWG\Property(
     *                       property="id",
     *                       type="integer",
     *                       example=1644700234
     *                   ),
     *                   @SWG\Property(
     *                       property="type",
     *                       type="string",
     *                       example="poynt"
     *                   )
     *               ),
     *           ),
     *           @SWG\Property(
     *               property="by_spending",
     *               type="object",
     *               @SWG\Property(
     *                   property="uuid",
     *                   type="string",
     *                   example="dc640254-9745-4c52-be99-b44fbe0029c7"
     *               ),
     *               @SWG\Property(
     *                   property="first_name",
     *                   type="string",
     *                   example="Test"
     *               ),
     *               @SWG\Property(
     *                   property="last_name",
     *                   type="integer",
     *                   example="User"
     *               ),
     *               @SWG\Property(
     *                   property="points",
     *                   type="integer",
     *                   example=100
     *               ),
     *               @SWG\Property(
     *                   property="channels",
     *                   type="object",
     *                   @SWG\Property(
     *                       property="id",
     *                       type="integer",
     *                       example=1644700234
     *                   ),
     *                   @SWG\Property(
     *                       property="type",
     *                       type="string",
     *                       example="poynt"
     *                   )
     *               ),
     *           ),
     *           @SWG\Property(
     *               property="by_transactions",
     *               type="object",
     *               @SWG\Property(
     *                   property="uuid",
     *                   type="string",
     *                   example="dc640254-9745-4c52-be99-b44fbe0029c7"
     *               ),
     *               @SWG\Property(
     *                   property="first_name",
     *                   type="string",
     *                   example="Test"
     *               ),
     *               @SWG\Property(
     *                   property="last_name",
     *                   type="integer",
     *                   example="User"
     *               ),
     *               @SWG\Property(
     *                   property="points",
     *                   type="integer",
     *                   example=100
     *               ),
     *               @SWG\Property(
     *                   property="channels",
     *                   type="object",
     *                   @SWG\Property(
     *                       property="id",
     *                       type="integer",
     *                       example=1644700234
     *                   ),
     *                   @SWG\Property(
     *                       property="type",
     *                       type="string",
     *                       example="poynt"
     *                   )
     *               ),
     *           )
     *       )
     *   ),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function dashboard_tops(Request $request, Project $project, Loyalty $loyalty) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $member = null;

        $tmp = elastic()->search([
            'index' => \App\LoyaltyMember::$index,
            'type' => \App\LoyaltyMember::$type,
            'body' => [
                'size' => 1,
                'query' => [
                    'nested' => [
                        'path' => 'projects',
                        'query' => [
                            'term' => [
                                'projects.id' => $project->id
                            ]
                        ]
                    ]
                ]
            ]
        ])['hits']['hits'];

        if (isset($tmp[0])) {
            $member = [
                'uuid'       => $tmp[0]['_source']['uuid'],
                'first_name' => $tmp[0]['_source']['first_name'],
                'last_name'  => $tmp[0]['_source']['last_name'],
                'points'     => 100,
                'channels' => $loyalty->channels->map(function($channel) {
                    return [
                        'id' => $channel->getRouteKey(),
                        'type' => $channel->type,
                    ];
                })
            ];
        }

        return [
            'by_points' => $member,
            'by_spending' => $member,
            'by_transactions' => $member,
        ];
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/leaderboard",
     *   description="Retrieve paginated list of members of loyalty",
     *   operationId="loyalty.leaderboard",
     *
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
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          type="integer",
     *          in="query"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function leaderboard(Request $request, Project $project, Loyalty $loyalty) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $page = $request->get('page', 1);
        $per_page = 10;
        $offset = max(0, $per_page * ($page - 1));

        $hits = elastic()->search([
            'index' => \App\LoyaltyMember::$index,
            'type'  => \App\LoyaltyMember::$type,
            'size'  => $per_page,
            'from'  => $offset,
            'body'  => [
                'query' => [
                    'nested' => [
                        'path' => 'projects',
                        'query' => [
                            'term' => [
                                'projects.id' => $project->id
                            ]
                        ]
                    ]
                ]
            ]
        ])['hits'];

        $members = collect($hits['hits'])->map(function($member) use ($loyalty) {
            return [
                'uuid'       => $member['_source']['uuid'],
                'first_name' => $member['_source']['first_name'],
                'last_name'  => $member['_source']['last_name'],
                'points'     => 100,
                'spending'   => 30,
                'channels'   => $loyalty->channels->map(function($channel) {
                    return [
                        'id' => $channel->getRouteKey(),
                        'type' => $channel->type,
                    ];
                })
            ];
        });

        $paginator = new LengthAwarePaginator($members, $hits['total'], $per_page, $page, ['pageName' => 'page', 'path' => url('/api/v1/project/' . $project->getRouteKey() . '/loyalties/' . $loyalty->getRouteKey() . '/leaderboard')]);

        return $paginator;
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/leaderboard/search",
     *   description="Retrieve paginated list of filtered members by search",
     *   operationId="loyalty.leaderboard.search",
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
     *          name="loyalty_id",
     *          description="ID of the loyalty program",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          type="integer",
     *          in="query"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          required=true,
     *          in="body",
     *
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="search",
     *                  description="Search string",
     *                  type="string",
     *                  example="Test"
     *              )
     *          )
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function leaderboard_search(Request $request, Project $project, Loyalty $loyalty) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->validate($request, [
            'search' => 'required|min:4'
        ]);

        $search = explode(' ', trim($request->search));
        $search = '*' . implode('*~ *', $search) . '*~';


        $page = $request->get('page', 1);
        $per_page = 10;
        $offset = max(0, $per_page * ($page - 1));

        $hits = elastic()->search([
            'index' => \App\LoyaltyMember::$index,
            'type'  => \App\LoyaltyMember::$type,
            'size'  => $per_page,
            'from'  => $offset,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => 'projects',
                                    'query' => [
                                        'term' => [
                                            'projects.id' => $project->id
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'query_string' => [
                                    'query' => 'full_name:'.$search . ' OR email:'.$search,
                                    'analyze_wildcard' => true,
                                    'default_operator' => 'AND',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ])['hits'];

        $members = collect($hits['hits'])->map(function($member) use ($loyalty) {
            return [
                'uuid'       => $member['_source']['uuid'],
                'first_name' => $member['_source']['first_name'],
                'last_name'  => $member['_source']['last_name'],
                'points'     => 100,
                'spending'   => 30,
                'channels'   => $loyalty->channels->map(function($channel) {
                    return [
                        'id' => $channel->getRouteKey(),
                        'type' => $channel->type,
                    ];
                })
            ];
        });

        $paginator = new LengthAwarePaginator($members, $hits['total'], $per_page, $page, ['pageName' => 'page', 'path' => url('/api/v1/project/' . $project->getRouteKey() . '/loyalties/' . $loyalty->getRouteKey() . '/leaderboard')]);

        return $paginator;
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/project/{project_id}/loyalties/{loyalty_id}/leaderboard/member/{uuid}",
     *   description="Retrieve a memmber of a loyalty and his metrics",
     *   operationId="loyalty.leaderboard.member",
     *
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
     *   @SWG\Response(
     *       response=200,
     *       description="successful operation",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(
     *               property="member",
     *               type="object",
     *               @SWG\Property(
     *                   property="uuid",
     *                   type="string",
     *                   example="dc640254-9745-4c52-be99-b44fbe0029c7"
     *               ),
     *               @SWG\Property(
     *                   property="first_name",
     *                   type="string",
     *                   example="Test"
     *               ),
     *               @SWG\Property(
     *                   property="last_name",
     *                   type="integer",
     *                   example="User"
     *               ),
     *               @SWG\Property(
     *                   property="email",
     *                   type="string",
     *                   example="example@example.com"
     *               ),
     *               @SWG\Property(
     *                   property="phone",
     *                   type="string",
     *                   example="+393390000000"
     *               ),
     *               @SWG\Property(
     *                   property="created_at",
     *                   type="string",
     *                   example="2018-05-31T16:53:03+0000"
     *               ),
     *               @SWG\Property(
     *                   property="subscribed_at",
     *                   type="string",
     *                   example="2018-05-31T16:53:03+0000"
     *               ),
     *               @SWG\Property(
     *                   property="note",
     *                   type="string",
     *                   example="Lorem ipsum dolor sit amet."
     *               )
     *           ),
     *           @SWG\Property(
     *               property="points",
     *               type="object",
     *               @SWG\Property(
     *                   property="total",
     *                   type="integer",
     *                   example=100
     *               ),
     *               @SWG\Property(
     *                   property="used",
     *                   type="integer",
     *                   example=20
     *               ),
     *               @SWG\Property(
     *                   property="unused",
     *                   type="integer",
     *                   example=80
     *               )
     *           ),
     *           @SWG\Property(
     *               property="channels",
     *               type="integer",
     *               example=1
     *           ),
     *           @SWG\Property(
     *               property="interactions",
     *               type="integer",
     *               example=5
     *           ),
     *           @SWG\Property(
     *               property="last_interaction",
     *               type="integer",
     *               example=30
     *           )
     *       )
     *   ),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project/loyalty/member not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function member(Request $request, Project $project, Loyalty $loyalty, $uuid) {
        if ($loyalty->project->id != $project->id) {
            return response()->json([
                'error' => 'Loyalty doesn\'t belong to this project.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $member = elastic()->get([
                'index' => LoyaltyMember::$index,
                'type'  => LoyaltyMember::$type,
                'id'    => $uuid,
            ])['_source'];

            $crm = elastic()->get([
                'index' => \App\CRM::$index,
                'type'  => \App\CRM::$type,
                'id'    => $project->id . '_' . $uuid,
            ])['_source'];

            $note    = null;
            $address = null;

            if (isset($crm['custom_fields'])) {
                $note = collect($crm['custom_fields'])->where('category', 'Loyalty')->where('name', 'Note')->first()['val_text'];
            }

            if (isset($crm['addresses'])) {
                $address = collect($crm['addresses'])->where('type', 'crm')->where('label', '_loyalty')->first()['formatted'];
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

        return [
            'member' => [
                'uuid'          => $uuid,
                'first_name'    => $member['first_name'],
                'last_name'     => $member['last_name'],
                'email'         => strtolower($member['email']),
                'phone'         => $member['phone'],
                'created_at'    => $member['created_at'],
                'subscribed_at' => $projects[$project->id]['subscribed_at'],
                'address'       => $address,
                'note'          => $note,
            ],
            'points' => [
                'total'  => 10, // TODO: mock
                'used'   => 2, // TODO: mock
                'unused' => 8, // TODO: mock
            ],
            'channels'          => 1,
            'interactions'      => 5, // TODO: mock
            'last_interaction' => 30, // TODO: mock
        ];
    }
}