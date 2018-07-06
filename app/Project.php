<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use App\CRM;

class Project extends Model
{
    use FakeIdTrait;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany('App\User')
            ->withPivot('role')
            ->orderByRaw("role = 'owner' DESC, role = 'admin' DESC, role = 'user' DESC");
    }

    public function admins()
    {
        return $this->belongsToMany('App\User')
            ->wherePivotIn('role', ['admin','owner']);
    }

    public function owners()
    {
        return $this->belongsToMany('App\User')
            ->wherePivotIn('role', ['owner']);
    }

    public function channels()
    {
        return $this->belongsToMany('App\Channel')->withPivot('id');
    }

    public function assigned_channels()
    {
        return $this->belongsToMany('App\Channel')->where('status', 'assigned');
    }

    public function rewards() {
        return $this->hasMany('App\Reward');
    }

    public function csv() {
        return $this->hasMany('App\Csv');
    }

    public function invites()
    {
        return $this->hasMany('App\Invite')->orderBy('valid_till', 'desc');
    }

    public function loyalties() {
        return $this->hasMany('App\Loyalty');
    }

    public function canAdmin(User $user) {
        return $this->admins->contains($user);
    }

    public function canOwn(User $user) {
        return $this->owners->contains($user);
    }

    public function toApi() {
        $array = $this->toArray();

        array_set(
            $array,
            $this->getKeyName(),
            $this->getRouteKey()
        );

        return $array;
    }

    public function uuid_from_email($email) {
        $uuids = collect(elastic()->search([
            'index' => CRM::$index,
            'type'  => 'users',
            'body'  => [
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'must' => [
                            ['term' => ['project' => $this->id]],
                        ],
                        'should' => [
                            [
                                'nested' => [
                                    'path' => 'channels',
                                    'query' => [
                                        'term' => ['channels.email.raw' => $email],
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
                ]
            ]
        ])['hits']['hits'])->pluck('_source.uuid');

        if ($uuids->isEmpty()) {
            throw new \Exception('User not found');
        }

        if ($uuids->count() > 1) {
            throw new \Exception('Multiple users found');
        }

        return $uuids[0];
    }
}
