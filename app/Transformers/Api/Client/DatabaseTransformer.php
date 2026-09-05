<?php

namespace Pterodactyl\Transformers\Api\Client;

use Pterodactyl\Models\Database;
use League\Fractal\Resource\Item;
use Pterodactyl\Models\Permission;
use League\Fractal\Resource\NullResource;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Contracts\Extensions\HashidsInterface;

class DatabaseTransformer extends BaseClientTransformer
{
    protected array $availableIncludes = ['password'];

    private Encrypter $encrypter;

    private HashidsInterface $hashids;

    /**
     * Handle dependency injection.
     */
    public function handle(Encrypter $encrypter, HashidsInterface $hashids)
    {
        $this->encrypter = $encrypter;
        $this->hashids = $hashids;
    }

    public function getResourceName(): string
    {
        return Database::RESOURCE_NAME;
    }

    public function transform(Database $model): array
    {
        $model->loadMissing('host');
        $host = $model->getRelation('host');
        $displayHost = config('pterodactyl.client_features.databases.display_host');
        $displayPort = config('pterodactyl.client_features.databases.display_port');

        return [
            'id' => $this->hashids->encode($model->id),
            'host' => [
                'address' => is_string($displayHost) && $displayHost !== '' ? $displayHost : $host->host,
                'port' => is_numeric($displayPort) ? (int) $displayPort : $host->port,
            ],
            'name' => $model->database,
            'username' => $model->username,
            'connections_from' => $model->remote,
            'max_connections' => $model->max_connections,
        ];
    }

    /**
     * Include the database password in the request.
     */
    public function includePassword(Database $database): Item|NullResource
    {
        if (!$this->request->user()->can(Permission::ACTION_DATABASE_VIEW_PASSWORD, $database->server)) {
            return $this->null();
        }

        return $this->item($database, function (Database $model) {
            return [
                'password' => $this->encrypter->decrypt($model->password),
            ];
        }, 'database_password');
    }
}
