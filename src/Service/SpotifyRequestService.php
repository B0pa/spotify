<?php

namespace App\Service;

use App\Entity\Track;
use App\Entity\Artist;
use App\Factory\ArtistFactory;
use App\Factory\TrackFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\TrackRepository;
use App\Repository\ArtistRepository;


readonly class SpotifyRequestService
{

    public function __construct(private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory,
                                private readonly ArtistFactory       $artistFactory,)
    {}

    public function searchTracks(string $track, string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $track . '&type=track&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $this->trackFactory->createMultipleFromSpotifyData($response->toArray()['tracks']['items']);
    }

    public function getTrack(string $spotifyId,string $token): Track
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $spotifyId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $this->trackFactory->createFromSpotifyData($response->toArray(),true);
    }

    public function searchArtist(string $artist, string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $artist . '&type=artist&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $this->artistFactory->createMultipleFromSpotifyData($response->toArray()['artists']['items']);
    }

    public function getArtist(string $spotifyId,string $token): Artist
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $spotifyId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $this->artistFactory->createFromSpotifyData($response->toArray());
    }

    public function getArtistsByIds(array $spotifyIds, string $token): array
    {
        $idsParam = implode(',', $spotifyIds);

        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists?ids=' . $idsParam, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return $this->artistFactory->createMultipleFromSpotifyData($response->toArray()['artists']);
    }

}