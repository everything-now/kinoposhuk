<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMoviesListRequest;
use App\API\TheMovieDB;
use Carbon\Carbon;

class MoviesController extends Controller
{
    protected $theMoviedDB;

    public function __construct()
    {
        $this->theMoviedDB = new TheMovieDB(env('THE_MOVIE_DB_API_KEY'));
    }

    /**
     * Get movie
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function get(int $id)
    {
        $movie = $this->theMoviedDB->getMovie($id);

        if (!$movie) {
            abort(404);
        }

        return response()->json($this->transformMovieResponseItem($movie));
    }

    /**
     * Get movies list
     *
     * @param GetMoviesListRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getList(GetMoviesListRequest $request)
    {
        $filterBy = $request->get('filter', 'popularity');
        $query = $request->get('query', '');
        $page = $request->get('page', 1);

        $movies = $this->theMoviedDB->getMovies($filterBy, $page, $query);

        $movies['results'] = collect($movies['results'])->transform(function ($item) {
            return $this->transformMovieResponseItem($item);
        });

        return response()->json($movies);
    }

    /**
     * Transform movie for response
     *
     * @param array $item
     * @return array
     */
    protected function transformMovieResponseItem(array $item) : array
    {
        $responseItem = [];
        $responseItem['id'] = $item['id'];
        $responseItem['title'] = $item['title'];
        $responseItem['raiting'] = $item['vote_average'];
        $responseItem['overview'] = $item['overview'];
        $responseItem['release'] = Carbon::parse($item['release_date'])->translatedFormat('d M. Y');
        $responseItem['image'] = 'https://image.tmdb.org/t/p/w220_and_h330_face' . $item['poster_path'];

        return $responseItem;
    }
}
