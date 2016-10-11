{*****************}
{***ueppy3.1.00***}
{*****************}
{**  CHANGELOG  **}
{**************************************************************************************************}
{** v.3.1.00 (01/01/2013)                                                                        **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}

<!-- GESTORE MAPPA - INIZIO -->

{******************************************************************}
{* COMPONENTE PER LA GESTIONE DELLE MAPPE (GOOGLEMAP)             *}
{*                                                                *}
{* Per implementare il componente è necessario includere:         *}
{*                                                                *}
{*                                                                *}
{* javascript: /admin/generic/gestore_mappa.js                    *}
{*             http://maps.googleapis.com/maps/api/js?sensor=true *}
{*                                                                *}
{* css       : /admin/generic/gestore_mappa.css                   *}
{*                                                                *}
{* tpl       : /admin/generic/gestore_mappa.css (questo file)     *}
{*             nella posizione in cui si vuole far apparire il    *}
{*             bottone per l'apertura della mappa                 *}
{*                                                                *}
{* CONFIGURAZIONE LATO PHP                                        *}
{*                                                                *}
{* Creare e assegnare a smarty l'array $map con le seguenti       *}
{* proprietà:                                                     *}
{*                                                                *}
{* $map['lat']            - latitudine (può essere settata a 0    *}
{*                                                   di default)  *}
{* $map['lng']            - longitudine (può essere settata a 0   *}
{*                                                    di default) *}
{* $map['zoom']           - livello di zoom (intero come da       *}
{*                          specifiche google da 0 a 15)          *}
{*                                                                *}
{* $map['address_fields'] - elenco degli elementi html da cui     *}
{*                          prelevare il valore per riempire il   *}
{*                          campo indirizzo separati da spazio    *}
{*                          es.                                   *}
{*                          #via .cap #provincia Italia           *}
{*                          Verranno prelevati i valori           *}
{*                          dell'elemento con id "via",           *}
{*                          classe "cap", con id "provincia" e a  *}
{*                          questi verrà aggiunta la stringa      *}
{*                          "Italia"                              *}
{*                                                                *}
{*                                                                *}
{* RISULTATI RESTITUITI                                           *}
{*                                                                *}
{* mapLat : Latitudine                                            *}
{* mapLng : Longitudine                                           *}
{* mapZoom: Livello di zoom                                       *}
{*                                                                *}
{******************************************************************}

<input type="button" id="mapTrigger" value="{getLang module="mappa" key="OPEN_MAP"}" />

<div id="mapcontainer">
  <input type="hidden" id="mapAddressFields" value="{$map.address_fields}" />
  <input type="hidden" id="mapZoom" name="mapZoom" value="{$map.zoom}" />

  <div class="mappa">
  </div>

  <div class="mapFieldsContainer">

    <div class="instructions">{getLang module="mappa" key="INSTRUCTIONS" htmlallowed="true"}</div>

    <label>
       <span>{getLang module="mappa" key="LONGITUDINE"}</span>
       <input type="text" name="mapLng" id="mapLng" value="{$map.lng}" />
    </label>

    <label>
       <span>{getLang module="mappa" key="LATITUDE"}</span>
       <input type="text" name="mapLat" id="mapLat" value="{$map.lat}" />
    </label>

    <input type="button" id="centerCoords" value="{getLang module="mappa" key="CENTER_COORDS"}" />


    <label>
       <span>{getLang module="mappa" key="ADDRESS"}</span>
    </label>

    <textarea id="mapAddress"></textarea>

    <input type="button" id="centerAddr" value="{getLang module="mappa" key="CENTER_ADDR"}" />

  </div>
</div>
<!-- GESTORE MAPPA - FINE -->