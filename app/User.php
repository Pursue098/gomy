<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use SammyK\LaravelFacebookSdk\SyncableGraphNodeTrait;
use Jrean\UserVerification\Traits\UserVerification;
use Laravel\Cashier\Billable;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use Laratrust\Traits\LaratrustUserTrait;


class User extends Authenticatable
{
    use Notifiable, FakeIdTrait, SyncableGraphNodeTrait, HasApiTokens, UserVerification, LaratrustUserTrait, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'company','business_id','device_id','vat_number','sale_points','address','company_address'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'fb_access_token',
    ];

    public function projects()
    {
        return $this->belongsToMany('App\Project')->withPivot('role');
    }

    public function invites()
    {
        return $this->hasMany('App\Invite');
    }

    /**
     * One-to-many: user can perform one or many payments for multiple channels.
     */
    public function payments()
    {
        return $this->hasMany('App\Payment');
    }

    /**
     * Restituisce gli altri utenti che hanno
     * progetti in comune con l'utente corrente
     *
     * @param  boolean $exclude Progetto da escludere
     * @return Collection       Elenco di utenti
     */
    public function teammates(Project $exclude = null)
    {
        $projects = $this->projects->pluck('id');

        return User::join('project_user', 'users.id', '=', 'project_user.user_id')
            ->where('user_id', '!=', $this->id)
            ->whereIn('project_id', $projects)
            ->when($exclude, function($query) use ($exclude) {
                return $query->whereNotIn('user_id', $exclude->users()->pluck('users.id'));
            })
            ->groupBy(['users.id', 'name', 'email'])
            ->get(['users.id', 'name', 'email']);
    }

    public function getTimezoneAttribute() {
        return 'Europe/Rome';
    }

    public function getPictureAttribute() {
        if (isset($this->fb_id)) {
            return '//graph.facebook.com/' . $this->fb_id . '/picture?width=160&amp;height=160';
        }

        return gravatar($this->email);
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications($limit = 10)
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
                            ->orderBy('created_at', 'desc')->limit($limit);
    }

    public function isTeia() {
        return (substr(strrchr($this->email, '@'), 1) == 'teia.company');
    }
}
