<?php

namespace App\API;

use Illuminate\Support\Facades\Http;

class TheMovieDB
{
    private $apiKey;
    private $baseUri;
    private $lang;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUri = 'https://api.themoviedb.org/3';
        $this->lang = str_replace('_', '-', app()->getLocale());
    }

    /**
     * Get list of movies
     *
     * @param string $filterBy
     * @param integer $page
     * @param string $query
     * @return array
     */
    public function getMovies(string $filterBy = 'release', $page = 1, string $query = '') : array
    {
        switch ($filterBy) {
            case 'release':
                $movies = $this->discoverMovieRequest([
                    'sort_by' => 'primary_release_date.desc',
                    'primary_release_date.lte' => today()->format('Y-m-d'),
                    'page' => $page
                ]);
                break;
            case 'query':
                $movies = $this->searchMovieRequest($query, [
                    'page' => $page
                ]);
                break;
            default:
                $movies = $this->discoverMovieRequest([
                    'sort_by' => 'popularity.desc',
                    'page' => $page
                ]);
                break;
        }

        return $movies;
    }

    /**
     * Get movie
     *
     * @param int $id
     * @return array
     */
    public function getMovie(int $id) : array
    {
        $response = $this->getRequest('/movie/' . $id);

        if (isset($response['success']) && !$response['success']) {
            return null;
        }

        return $response;
    }

    /**
     * Make discover/movie request
     *
     * @param array $data
     * @return array
     */
    private function discoverMovieRequest(array $data) : array
    {
        return $this->getRequest('/discover/movie', $data);
    }

    /**
     * Make search/movie request
     *
     * @param string $query
     * @param array $data
     * @return array
     */
    private function searchMovieRequest(string $query, array $data) : array
    {
        return $this->getRequest('/search/movie', $data + [
            'query' => $query,
        ]);
    }

    /**
     * Make get request
     *
     * @param string $path
     * @param array $data
     * @return array
     */
    private function getRequest(string $path, array $data = []) : array
    {
        $url = $this->baseUri . $path;
        $data = $data + [
            'api_key' => $this->apiKey,
            'language' => $this->lang,
        ];

        return Http::get($url, $data)->json();
    }
}
