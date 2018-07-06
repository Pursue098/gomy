<?php

namespace App\Http\Controllers\ApiController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use Symfony\Component\HttpFoundation\Response;


class ProjectController extends Controller
{
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return response()->json([
            'error' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/projects",
     *   description="Create a new Project",
     *   summary="Create a Project",
     *   operationId="project.create",
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(
     *          name="body",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/project")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="project",
     *   type="object",
     *   description="Project",
     *   required={"name"},
     *
     *   @SWG\Property(
     *          property="name",
     *          description="Project name",
     *          type="string",
     *          example="Default Poynt Project"
     *   )
     * )
     */
    public function create(Request $request) {
        $this->validate($request, [
            'name'   => 'required|max:191|regex:/^[-\' \p{L}\d]+$/u'
        ]);

        $project = Project::create([
            'name' => $request->get('name'),
        ]);

        $request->user()->projects()->attach($project, ['role' => 'owner']);

        return response()->json([
            'data' => [
                'project' => $project->toApi()
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/api/v1/projects/{project_id}/channels/poynts",
     *   description="Create a new Poynt Channel",
     *   summary="Create a new Poynt Channel",
     *   operationId="project.channels.poynt.create",
     *   consumes={"application/json"},
     *
     *  @SWG\Parameter(
     *          name="project_id",
     *          description="ID of the project",
     *          required=true,
     *          type="integer",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="body",
     *          required=true,
     *          in="body",
     *          @SWG\Schema(ref="#/definitions/poynt_channel")
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=404, description="project not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @SWG\Definition(
     *   definition="poynt_channel",
     *   type="object",
     *   description="Poynt Channel",
     *   required={"name","business_id","device_id"},
     *
     *   @SWG\Property(
     *          property="name",
     *          description="Channel name",
     *          type="string",
     *          example="Default Poynt Channel"
     *   ),
     *   @SWG\Property(
     *          property="business_id",
     *          description="Business ID",
     *          type="string",
     *          format="uuid",
     *          example="18f071cc-5ed4-4b33-80c1-305056d42bfb"
     *   ),
     *   @SWG\Property(
     *          property="device_id",
     *          description="Device ID",
     *          type="string",
     *          format="urn:tid:uuid",
     *          example="urn:tid:48c54303-6d51-39af-bdeb-4af53f621652"
     *   )
     * )
     */
    public function create_poynt_channel(Request $request, Project $project) {
        $this->validate($request, [
            'name'        => 'required|max:50|regex:/^[-\' \p{L}\d]+$/u',
            'business_id' => 'required|uuid|unique:ch_poynt,business_id',
            'device_id'   => 'required|tid|unique:ch_poynt_device,id',
        ]);

        $channel = $project->channels()->create([
            'name'   => $request->name,
            'type'   => 'poynt',
        ]);

        $poynt = \App\Channels\Poynt::create([
            'business_id' => $request->business_id,
            'status'     => 'grabbed', // generally the status is "grabbing" and we run a job that grab historical data. As this is a fake channel there is no data to grab, so we set it as grabbed
        ]);

        $channel->status = 'assigned';

        $poynt->channel()->save($channel);

        $poynt->devices()->create([
            'id' => $request->device_id
        ]);

        return response()->json([
            'data' => [
                'channel' => $channel->toApi()
            ],
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/api/v1/projects/channels/poynts",
     *   description="Find a Poynt Project and Channel by business_id and device_id",
     *   summary="Find a Poynt Project and Channel by business_id and device_id",
     *   operationId="project.channels.poynt.find",
     *
     *  @SWG\Parameter(
     *          name="business_id",
     *          description="Business ID",
     *          required=true,
     *          type="string",
     *          format="uuid",
     *          in="query"
     *   ),
     *   @SWG\Parameter(
     *          name="device_id",
     *          description="Device ID",
     *          required=true,
     *          type="string",
     *          format="urn:tid:uuid",
     *          in="query"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="not acceptable"),
     *   @SWG\Response(response=401, description="unauthorized"),
     *   @SWG\Response(response=403, description="another channel is already associated"),
     *   @SWG\Response(response=404, description="channel not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     */
    public function find_by_business_and_device(Request $request) {
        $this->validate($request, [
            'business_id' => 'required|uuid', // |exists:ch_poynt,business_id
            'device_id'   => 'required|tid', // |exists:ch_poynt_device,id
        ]);

        $poynt = \App\Channels\Poynt::where('business_id', $request->business_id)->first();

        if (! $poynt) {
            return response()->json([
                'error' => 'Channel not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $projects = $poynt->channel->projects->filter(function($project) use ($request) {
            return $project->users->contains($request->user());
        });

        if ($projects->isEmpty()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $device = $poynt->devices->keyBy('id')->get($request->device_id);

        if ($poynt->devices->isNotEmpty() && ! $device) {
            return response()->json([
                'error' => 'Another device is already associated', // TODO: should we support that?
            ], Response::HTTP_FORBIDDEN);
        }

        return $projects->sortBy('id')->map(function($project) use ($poynt) {
            return [
                'project_id' => $project->getRouteKey(),
                'channel_id' => $poynt->channel->getRouteKey(),
            ];
        });
    }
}
