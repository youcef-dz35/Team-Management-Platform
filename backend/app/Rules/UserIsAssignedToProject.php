<?php

namespace App\Rules;

use App\Models\Project;
use Illuminate\Contracts\Validation\Rule;

class UserIsAssignedToProject implements Rule
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function passes($attribute, $value)
    {
        $project = Project::find($this->projectId);
        if (!$project) {
            return false;
        }

        return $project->users->contains($value);
    }

    public function message()
    {
        return 'The selected user is not assigned to this project.';
    }
}
