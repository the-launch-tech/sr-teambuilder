<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
  protected $table = 'users';

  protected $primaryKey = 'id';

  protected $fillable = ['first_name', 'last_name', 'ranking', 'can_play_goalie', 'user_type'];

  public function scopeIsPlayer($query) {
    return $query->where('user_type', 'player');
  }

  public function scopeCanPlayGoalie($query) {
    return $query->where('can_play_goalie', 1);
  }

  public function scopeByRanking($query) {
    return $query->orderby('ranking', 'DESC');
  }

  public static function getPlayers() {
    return self::isPlayer()->get();
  }

  public static function total() {
    return self::isPlayer()->count();
  }
}
