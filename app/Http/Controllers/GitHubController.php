<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Support\Str;

class GitHubController extends Controller
{
    public function fetchRepositories()
    {
        $author = env('GITHUB_USERNAME', 'MarcoZellini');

        //sostituire la richiesta quando le chiamate a github si sbloccano
        $response = Http::withoutVerifying()->withHeader('Authorization', 'Bearer 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')->get("https://api.github.com/users/francesco-munafo/repos?sort=created&direction=asc&per_page=100");

        $repositories = $response->json();

        foreach ($repositories as $repository) {

            $project = Project::updateOrCreate(
                [
                    'title' => $repository['name']
                ],
                [
                    'title' => $repository['name'],
                    'slug' => Project::generateSlug($repository['name']),
                    'description' => $repository['description'],
                    'cover_image' => 'placeholders/L3PymiCpURxhE12JTZwHsKpF46u8cGKbiwihdXIA.png',
                    'github_link' => $repository['html_url']
                ]
            );

            //sostituire la richiesta quando le chiamate a github si sbloccano
            $lang_response = Http::withoutVerifying()->withHeader('Authorization', 'Bearer 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')->get("https://api.github.com/repos/{$author}/{$repository['name']}/languages");

            if ($lang_response->successful()) {
                $languages = array_keys($lang_response->json());
                //$languagesPercentage = array_values($lang_response->json()); //TODO GET PERCENTAGE

                $technologyIds = [];

                foreach ($languages as $language) {
                    $technology = Technology::firstOrCreate(
                        ['name' => $language],
                        ['slug' => Technology::generateSlug($language)]
                    );
                    $technologyIds[] = $technology->id;
                }
                $project->technologies()->sync($technologyIds);

                //TODO: Generate a project type based on languages
                $project->type_id = rand(1, 5);
            }
        }
        return to_route('admin.projects.index')->with('message', 'Fetch Data From GitHub Successfully');
    }
}
