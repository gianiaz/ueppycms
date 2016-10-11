<ul class="list-inline text-center">
  {if $widgetData.socials.Twitter}
    <li>
      <a target="_blank" href="{$widgetData.socials.Twitter}">
                                <span class="fa-stack fa-lg">
                                    <i class="fa fa-circle fa-stack-2x"></i>
                                    <i class="fa fa-twitter fa-stack-1x fa-inverse"></i>
                                </span>
      </a>
    </li>
  {/if}
  {if $widgetData.socials.Facebook}
    <li>
      <a target="_blank" href="{$widgetData.socials.Facebook}">
                                <span class="fa-stack fa-lg">
                                    <i class="fa fa-circle fa-stack-2x"></i>
                                    <i class="fa fa-facebook fa-stack-1x fa-inverse"></i>
                                </span>
      </a>
    </li>
  {/if}
  {if $widgetData.socials.Github}
    <li>
      <a target="_blank" href="{$widgetData.socials.Github}">
                                <span class="fa-stack fa-lg">
                                    <i class="fa fa-circle fa-stack-2x"></i>
                                    <i class="fa fa-github fa-stack-1x fa-inverse"></i>
                                </span>
      </a>
    </li>
  {/if}
</ul>