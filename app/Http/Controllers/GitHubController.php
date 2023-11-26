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
        $response = Http::withoutVerifying()->get("https://api.github.com/users/francesco-munafo/repos?sort=created&direction=asc&per_page=100");
        //$response = Http::withoutVerifying()->get("https://5247f9c2-b00d-4d81-b23b-aaf483dd9534.mock.pstmn.io/https://api.github.com/users/{$author}/repos?per_page=100");


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
            $lang_response = Http::withoutVerifying()->get("https://api.github.com/repos/{$author}/{$repository['name']}/languages");
            //$lang_response = Http::withoutVerifying()->get("https://5247f9c2-b00d-4d81-b23b-aaf483dd9534.mock.pstmn.io/https://api.github.com/repos/MarcoZellini/laravel-boolean/languages");

            //associative array ['technology_name => bytes]
            $project_technologies = $lang_response->json();

            foreach ($project_technologies as $technology_name => $bytes) {
                $technology = Technology::updateOrCreate(
                    ['name' => $technology_name],
                    [
                        'name' => $technology_name,
                        'slug' => Technology::generateSlug($technology_name)
                    ],
                );

                $technology_ids[] = Technology::where('slug', $technology->slug)->first()->id;
            }

            $project->technologies()->sync($technology_ids);

            //TODO: Generate a project type based on languages
            $project->type_id = rand(1, 5);
        }
        return to_route('admin.projects.index')->with('message', 'Fetch Data From GitHub Successfully');
    }
}
