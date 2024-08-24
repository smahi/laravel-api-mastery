<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                // // method one
                // 'emailVerifiedAt' => $this->when(
                //     // include it for all users.* routes
                //     $request->routeIs('users.*'),
                //     $this->email_verified_at
                // ),
                // // method one
                // 'createdAt' => $this->when(
                //     // include it for all users.* routes
                //     $request->routeIs('users.*'),
                //     $this->created_at
                // ),
                // // method one
                // 'updatedAt' => $this->when(
                //     // include it for all users.* routes
                //     $request->routeIs('users.*'),
                //     $this->updated_at
                // ),

                // a better alternative....
                // method two using 'mergeWhen()'
                $this->mergeWhen(
                    $request->routeIs('users.*'),
                    [
                        'emailVerifiedAt' => $this->email_verified_at,
                        'createdAt' => $this->created_at,
                        'updatedAt' => $this->updated_at
                    ]
                ),
            ]
        ];
    }
}
