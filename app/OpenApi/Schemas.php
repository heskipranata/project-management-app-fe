<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   title="User",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="Project",
 *   type="object",
 *   title="Project",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="start_date", type="string", format="date", nullable=true),
 *   @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *   @OA\Property(property="status", type="string", example="planning"),
 *   @OA\Property(property="owner", ref="#/components/schemas/User"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string"),
 *   @OA\Property(property="errors", type="object", description="Validation or other errors", additionalProperties=@OA\Property(type="string"))
 * )
 *
 * @OA\Schema(
 *   schema="ProfileUpdateRequest",
 *   type="object",
 *   @OA\Property(property="name", type="string", example="Jane Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *   @OA\Property(property="password", type="string", format="password", example="newpass123")
 * )
 *
 * @OA\Schema(
 *   schema="UpdateProfileResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Profile updated successfully"),
 *   @OA\Property(property="data", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *   schema="AuthLoginResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Login successfully"),
 *   @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." )
 * )
 *
 * @OA\Schema(
 *   schema="RegisterResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="User registered successfully")
 * )
 *
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *   title="Task",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="project", ref="#/components/schemas/Project"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="assigned_to", type="integer", format="int64", nullable=true),
 *   @OA\Property(property="assignee", ref="#/components/schemas/User"),
 *   @OA\Property(property="priority", type="string", example="medium"),
 *   @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *   @OA\Property(property="status", type="string", example="todo"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Schemas
{
    // This class only holds OpenAPI annotations.
}
