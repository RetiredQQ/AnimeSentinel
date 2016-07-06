<?php

namespace App;

use App\Scopes\CacheShowScope;
use Illuminate\Database\Eloquent\Model;

class Show extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'mal_id', 'title', 'alts', 'description', 'show_type', 'hits',
  ];

  /**
   * The attributes that should be mutated to dates.
   *
   * @var array
   */
  protected $dates = ['uploadtime', 'created_at', 'updated_at'];

  /**
   * The attributes that should be casted to native types.
   *
   * @var array
   */
  protected $casts = [
    'alts' => 'array',
  ];

  /**
  * Get all videos related to this show.
  *
  * @return \Illuminate\Database\Eloquent\Relations\Relation
  */
  public function videos() {
    return $this->hasMany(Video::class);
  }

  /**
  * Get all streamers for this show.
  *
  * @return \Illuminate\Database\Eloquent\Relations\Relation
  */
  public function streamers() {
    return $this->belongsToMany(Streamer::class, 'videos');
  }

  /**
  * Get the latest subbed episode number for this show.
  *
  * @return integer
  */
  public function getLatestSubAttribute() {
    $episode_num = $this->videos()->where('translation_type', 'sub')->max('episode_num');
    return $episode_num ? $episode_num : -1;
  }

  /**
  * Get the latest dubbed episode number for this show.
  *
  * @return integer
  */
  public function getLatestDubAttribute() {
    $episode_num =  $this->videos()->where('translation_type', 'dub')->max('episode_num');
    return $episode_num ? $episode_num : -1;
  }

  /**
  * Get the list of subbed episodes and their internal link.
  *
  * @return array
  */
  public function getEpisodesSubAttribute() {
    $episodes = $this->videos()
                     ->where('translation_type', 'sub')
                     ->distinct('episode_num')
                     ->orderBy('episode_num', 'asc')
                     ->get();
    return $episodes;
  }

  /**
  * Get the list of dubbed episodes and their internal link.
  *
  * @return array
  */
  public function getEpisodesDubAttribute() {
    $episodes = $this->videos()
                     ->where('translation_type', 'dub')
                     ->distinct('episode_num')
                     ->orderBy('episode_num', 'asc')
                     ->get();
    return $episodes;
  }

  /**
  * Get the full url for this show's details page.
  *
  * @return string
  */
  public function getDetailsUrlAttribute() {
    return url('/anime/'.$this->id);
  }

  /**
  * Get the url to this show's MAL page.
  *
  * @return string
  */
  public function getMalUrlAttribute() {
    return 'http://myanimelist.net/anime/'.$this->mal_id; // NOTE: MAL does not have https
  }

  /**
  * Handle caching for the shows information
  *
  * @return integer
  */
  public function getIdAttribute($value) {
    return $value;
  }
}
