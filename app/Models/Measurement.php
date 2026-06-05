<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id','label','poitrine','taille','hanches',
        'longueur_pantalon','longueur_manche','longueur_robe',
        'cou','epaules','entrejambe','bras','notes','is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function client() { return $this->belongsTo(Client::class); }

    public function toFormattedArray(): array {
        return [
            'Poitrine'          => $this->poitrine ? "{$this->poitrine} cm" : null,
            'Taille'            => $this->taille ? "{$this->taille} cm" : null,
            'Hanches'           => $this->hanches ? "{$this->hanches} cm" : null,
            'Longueur pantalon' => $this->longueur_pantalon ? "{$this->longueur_pantalon} cm" : null,
            'Longueur manche'   => $this->longueur_manche ? "{$this->longueur_manche} cm" : null,
            'Longueur robe'     => $this->longueur_robe ? "{$this->longueur_robe} cm" : null,
            'Cou'               => $this->cou ? "{$this->cou} cm" : null,
            'Épaules'           => $this->epaules ? "{$this->epaules} cm" : null,
            'Entrejambe'        => $this->entrejambe ? "{$this->entrejambe} cm" : null,
            'Bras'              => $this->bras ? "{$this->bras} cm" : null,
        ];
    }
}
