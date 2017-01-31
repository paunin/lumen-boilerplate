<?php
declare(strict_types=1);

namespace App\Models;

use App\Eloquent\Models\AbstractModel;
use App\Security\User\UserInterface as SecurityUserInterface;
use App\Traits\Model\UuidModelTrait;
use DateTime;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Socialite\Contracts\User as OauthUser;
use Swagger\Annotations as SWG;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @SWG\Definition(
 *     @SWG\Property(property="id", type="string", readOnly=true),
 *     @SWG\Property(property="username", type="string"),
 *     @SWG\Property(property="email", type="string"),
 *     @SWG\Property(property="createdAt", type="string", format="datetime"),
 *     @SWG\Property(property="updatedAt", type="string", format="datetime"),
 *     @SWG\Property(property="active", type="boolean"),
 *     @SWG\Property(
 *          property="roles",
 *          type="array",
 *          @SWG\Items(type="string")
 *     )
 * )
 */
class User extends AbstractModel implements
    AuthenticatableContract,
    AuthorizableContract,
    JWTSubject,
    SecurityUserInterface
{
    use Authenticatable, Authorizable;
    use UuidModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'active',
        'roles',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'roles'  => 'array',
        'active' => 'boolean',
    ];
    /**
     * @var array
     */
    protected $attributes = [
        'roles'  => [Role::ROLE_USER],
        'active' => true
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @param OauthUser $oauthUser
     *
     * @return User
     */
    public static function getOrCreateByOauth(OauthUser $oauthUser): self
    {
        /** @var self $user */
        $user = self::where('email', $oauthUser->getEmail())
                    ->first();

        if (!$user) {
            $user = new self(
                [
                    'email'    => $oauthUser->getEmail(),
                    'username' => $oauthUser->getNickname() ?: $oauthUser->getEmail(),
                    'active'   => true,
                    'roles'    => [Role::ROLE_USER],
                    'password' => \Hash::make(time()),
                ]
            );
            $user->save();
        }

        return $user;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return string
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Return user's roles.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->getAttribute('roles');
    }

    /**
     * @param string $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Set user password to null
     */
    public function unsetPassword()
    {
        $this->attributes['password'] = null;
    }

    /**
     * @param string $value
     */
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower($value);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->getAttribute('id');
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getAttribute('username');
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getAttribute('email');
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getAttribute('active');
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->getAttribute(self::UPDATED_AT);
    }
}
