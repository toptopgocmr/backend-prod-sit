<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'label', 'values',
        // Colonnes legacy conservées si elles existent
        'poitrine', 'taille', 'hanches',
        'longueur_pantalon', 'longueur_manche', 'longueur_robe',
        'cou', 'epaules', 'entrejambe', 'bras', 'notes', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'values'     => 'array',
    ];

    public function client() { return $this->belongsTo(Client::class); }

    /**
     * Retourne les mesures fusionnées : JSON values + colonnes legacy
     */
    public function getAllValues(): array
    {
        $json   = $this->values ?? [];
        $legacy = array_filter([
            'poitrine'          => $this->poitrine,
            'taille'            => $this->taille,
            'hanches'           => $this->hanches,
            'longueur_pantalon' => $this->longueur_pantalon,
            'longueur_manche'   => $this->longueur_manche,
            'longueur_robe'     => $this->longueur_robe,
            'cou'               => $this->cou,
            'epaules'           => $this->epaules,
            'entrejambe'        => $this->entrejambe,
            'bras'              => $this->bras,
        ], fn($v) => $v !== null);

        return array_merge($legacy, $json);
    }

    public function toFormattedArray(): array
    {
        $labels = [
            // Femme - haut / corsage
            'f_tour_poitrine'        => 'Poitrine',
            'f_tour_taille'          => 'Taille',
            'f_bustre'               => 'Bustre',
            'f_longueur_dos'         => 'Long. dos',
            'f_longueur_epaule'      => 'Épaule',
            'f_carrure_devant'       => 'Carrure devant',
            'f_decollte_devant'      => 'Décolleté devant',
            'f_pinces'               => 'Pinces',
            'f_emmanchure'           => 'Emmanchure',
            'f_tour_encolure'        => 'Tour encolure',
            'f_tour_manche'          => 'Tour de manche',
            // Femme - manches
            'f_manche_longue'        => 'Manche longue',
            'f_manche_courte'        => 'Manche courte',
            'f_manche_trois_quarts'  => 'Manche 3/4',
            // Femme - bas
            'f_tour_ceinture'        => 'Ceinture',
            'f_grandes_hanches'      => 'Hanche',
            'f_petites_hanches'      => 'Tour petites hanches',
            'f_fessier'              => 'Fessier',
            'f_tour_cuisse'          => 'Cuisse',
            'f_enfourchure'          => 'Enfourchure',
            'f_entrejambe'           => 'Entrejambe',
            // Femme - longueurs
            'f_robe_longue'          => 'Long. robe',
            'f_longueur_genoux'      => 'Long. genoux',
            'f_longueur_cheville'    => 'Long. cheville',
            'f_longueur_pantalon'    => 'Long. pantalon',
            'f_longueur_veste'       => 'Long. veste',
            // Femme - legacy / autres
            'f_hauteur_saillant'     => 'Hauteur saillant',
            'f_ecart_saillants'      => 'Écart des saillants',
            'f_hauteur_buste_devant' => 'Hauteur buste devant',
            'f_hauteur_buste_dos'    => 'Hauteur buste dos',
            'f_longueur_cote_buste'  => 'Longueur côté buste',
            'f_longueur_manche'      => 'Longueur de manche',
            'f_carrure_dos'          => 'Carrure dos',
            'f_tour_bassin'          => 'Tour de bassin',
            'f_hauteur_bassin'       => 'Hauteur bassin',
            'f_longueur_assise'      => "Longueur d'assise",
            'f_fourche_devant'       => 'Fourche devant',
            'f_fourche_dos'          => 'Fourche dos',
            'f_largeur_bas'          => 'Largeur du bas',
            'f_hauteur_fessier'      => 'Hauteur fessier',
            'f_robe_avant_genoux'    => 'Robe avant genoux',
            'f_robe_apres_genoux'    => 'Robe après genoux',
            'f_robe_trois_quarts'    => 'Robe trois quarts',
            'f_jupe_longue'          => 'Jupe longue',
            'f_jupe_genoux'          => 'Jupe genoux',
            'f_jupe_trois_quarts'    => 'Jupe trois quarts',
            // Homme - haut
            'h_epaule'               => 'Épaule',
            'h_manche_longue'        => 'Manche longue',
            'h_manche_courte'        => 'Manche courte',
            'h_tour_poitrine'        => 'Tour de poitrine',
            'h_tour_taille_ventre'   => 'Tour taille/ventre',
            'h_carrure_dos'          => 'Carrure dos',
            'h_longueur_chemise'     => 'Longueur chemise',
            'h_longueur_veste'       => 'Longueur veste',
            'h_contour_manche'       => 'Contour manche',
            'h_tour_col'             => 'Tour de col',
            'h_longueur_devant'      => 'Longueur devant',
            // Homme - bas
            'h_tour_ceinture'        => 'Tour de ceinture',
            'h_tour_bassin'          => 'Tour de bassin',
            'h_tour_cuisse'          => 'Tour de cuisse',
            'h_largeur_genoux'       => 'Largeur genoux',
            'h_tour_mollet'          => 'Tour de mollet',
            'h_diametre_bas'         => 'Diamètre du bas',
            'h_longueur_pantalon'    => 'Longueur pantalon',
            'h_longueur_culotte'     => 'Longueur culotte',
            'h_pisset'               => 'Pisset (braguette)',
            // Enfant
            'e_tour_poitrine'        => 'Tour de poitrine',
            'e_tour_taille'          => 'Tour de taille',
            'e_tour_hanches'         => 'Tour de hanches',
            'e_epaule'               => 'Épaule',
            'e_longueur_manche'      => 'Longueur manche',
            'e_longueur_veste'       => 'Longueur veste/robe',
            'e_tour_ceinture'        => 'Tour de ceinture',
            'e_longueur_pantalon'    => 'Longueur pantalon',
            'e_entrejambe'           => 'Entrejambe',
            'e_tour_cuisse'          => 'Tour de cuisse',
            // Legacy
            'poitrine'               => 'Poitrine',
            'taille'                 => 'Taille',
            'hanches'                => 'Hanches',
        