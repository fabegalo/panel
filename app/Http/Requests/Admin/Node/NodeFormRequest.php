<?php

namespace Pterodactyl\Http\Requests\Admin\Node;

use Pterodactyl\Rules\Fqdn;
use Pterodactyl\Models\Node;
use Pterodactyl\Rules\Hostname;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class NodeFormRequest extends AdminFormRequest
{
    /**
     * Get rules to apply to data in this request.
     */
    public function rules(): array
    {
        if ($this->method() === 'PATCH') {
            $data = Node::getRulesForUpdate($this->route()->parameter('node'));
        } else {
            $data = Node::getRules();
            $data['fqdn'][] = Fqdn::make('scheme');
        }

        $data['public_sftp_host'][] = new Hostname();

        return $data;
    }
}
