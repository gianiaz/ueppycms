<div class="main">
  <ul data-pause="{$widgetData.pause}" data-transizione="{$widgetData.transizione}">
    {foreach item=diapositiva from=$widgetData.items}
      <li>
        <img class="imgSld" src="{$diapositiva.img.url}" alt="{$diapositiva.img.alt}"/>
        <div class="descrizionePrdotto">
          <p class="offertaMese altTxt">{$diapositiva.testo}</p>
          <h3>{$diapositiva.titolo}</h3>
          <div class="prezzo">{$diapositiva.sottotitolo}</div>
          <a href="{$diapositiva.url}" class="continue"><span>{getLang module="ecommerce" key="ACQUISTA"}</span></a>
        </div>
      </li>
    {/foreach}
  </ul>
  <div id="sliderHomePager"></div>
</div>
