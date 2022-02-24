<?php
/**
 * @var \MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset $asset
 */
?>
@if(empty($asset))
    <script>
        document.getElementById('mcloud-mux-meta').remove();
    </script>
@else
    <div class="info-panel-contents">
        <div id="info-panel-tab-original">
            <div class="info-file-info">
                <div class="info-line">
                    <h3>Status</h3>
                    {{ucfirst($asset->status)}}
                </div>
                <div class="info-line">
                    <h3>Subtitles</h3>
                    @if(empty($asset->subtitles))
                    No subtitles
                    @else
                    <ul class="mux-asset-captions" data-nonce="{{wp_create_nonce('mux-delete-caption')}}" data-asset-id="{{$asset->id()}}">
                        @foreach($asset->subtitles as $subtitle)
                        <li data-track-id="{{$subtitle['id']}}">
                            <a href="#"><img class="logo" src="{{ILAB_PUB_IMG_URL}}/ilab-ui-icon-trash.svg"></a>
                            {{$subtitle['name']}} ({{$subtitle['language_code']}})
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            <div class="info-file-info" id="mux-captions-uploader" data-asset-id="{{$asset->id()}}" data-nonce="{{wp_create_nonce('mux-upload-caption')}}">
                <div class="info-line info-line-form">
                    <h3>Upload Subtitles</h3>
                    <div class="info-line-form-row">
                        <label for="mux-captions-language">Language</label>
                        <select id="mux-captions-language">
                            <option value='af'>Afrikaans</option>
                            <option value='af-ZA'>Afrikaans - South Africa</option>
                            <option value='ar'>Arabic</option>
                            <option value='ar-AE'>Arabic - United Arab Emirates</option>
                            <option value='ar-BH'>Arabic - Bahrain</option>
                            <option value='ar-DZ'>Arabic - Algeria</option>
                            <option value='ar-EG'>Arabic - Egypt</option>
                            <option value='ar-IQ'>Arabic - Iraq</option>
                            <option value='ar-JO'>Arabic - Jordan</option>
                            <option value='ar-KW'>Arabic - Kuwait</option>
                            <option value='ar-LB'>Arabic - Lebanon</option>
                            <option value='ar-LY'>Arabic - Libya</option>
                            <option value='ar-MA'>Arabic - Morocco</option>
                            <option value='ar-OM'>Arabic - Oman</option>
                            <option value='ar-QA'>Arabic - Qatar</option>
                            <option value='ar-SA'>Arabic - Saudi Arabia</option>
                            <option value='ar-SY'>Arabic - Syria</option>
                            <option value='ar-TN'>Arabic - Tunisia</option>
                            <option value='ar-YE'>Arabic - Yemen</option>
                            <option value='az'>Azeri</option>
                            <option value='az-AZ'>Cyrl Azeri (Cyrillic) - Azerbaijan</option>
                            <option value='az-AZ-Latn'>Azeri (Latin) - Azerbaijan</option>
                            <option value='be'>Belarusian</option>
                            <option value='be-BY'>Belarusian - Belarus</option>
                            <option value='bg'>Bulgarian</option>
                            <option value='bg-BG'>Bulgarian - Bulgaria</option>
                            <option value='ca'>Catalan</option>
                            <option value='ca-ES'>Catalan - Catalan</option>
                            <option value='cs'>Czech</option>
                            <option value='cs-CZ'>Czech - Czech Republic</option>
                            <option value='da'>Danish</option>
                            <option value='da-DK'>Danish - Denmark</option>
                            <option value='de'>German</option>
                            <option value='de-AT'>German - Austria</option>
                            <option value='de-CH'>German - Switzerland</option>
                            <option value='de-DE'>German - Germany</option>
                            <option value='de-LI'>German - Liechtenstein</option>
                            <option value='de-LU'>German - Luxembourg</option>
                            <option value='div'>Dhivehi</option>
                            <option value='div-MV'>Dhivehi - Maldives</option>
                            <option value='el'>Greek</option>
                            <option value='el-GR'>Greek - Greece</option>
                            <option value='en'>English</option>
                            <option value='en-AU'>English - Australia</option>
                            <option value='en-BZ'>English - Belize</option>
                            <option value='en-CA'>English - Canada</option>
                            <option value='en-CB'>English - Caribbean</option>
                            <option value='en-GB'>English - United Kingdom</option>
                            <option value='en-IE'>English - Ireland</option>
                            <option value='en-JM'>English - Jamaica</option>
                            <option value='en-NZ'>English - New Zealand</option>
                            <option value='en-PH'>English - Philippines</option>
                            <option value='en-TT'>English - Trinidad and Tobago</option>
                            <option value='en-US'>English - United States</option>
                            <option value='en-ZA'>English - South Africa</option>
                            <option value='en-ZW'>English - Zimbabwe</option>
                            <option value='es'>Spanish</option>
                            <option value='es-AR'>Spanish - Argentina</option>
                            <option value='es-BO'>Spanish - Bolivia</option>
                            <option value='es-CLe'>Spanish - Chile</option>
                            <option value='es-CO'>Spanish - Colombia</option>
                            <option value='es-CR'>Spanish - Costa Rica</option>
                            <option value='es-DO'>Spanish - Dominican Republic</option>
                            <option value='es-EC'>Spanish - Ecuador</option>
                            <option value='es-ES'>Spanish - Spain</option>
                            <option value='es-GT'>Spanish - Guatemala</option>
                            <option value='es-HN'>Spanish - Honduras</option>
                            <option value='es-MX'>Spanish - Mexico</option>
                            <option value='es-NI'>Spanish - Nicaragua</option>
                            <option value='es-PA'>Spanish - Panama</option>
                            <option value='es-PE'>Spanish - Peru</option>
                            <option value='es-PR'>Spanish - Puerto Rico</option>
                            <option value='es-PY'>Spanish - Paraguay</option>
                            <option value='es-SV'>Spanish - El Salvador</option>
                            <option value='es-UY'>Spanish - Uruguay</option>
                            <option value='es-VE'>Spanish - Venezuela</option>
                            <option value='et'>Estonian</option>
                            <option value='et-EE'>Estonian - Estonia</option>
                            <option value='eu'>Basque</option>
                            <option value='eu-ES'>Basque - Basque</option>
                            <option value='fa'>Farsi</option>
                            <option value='fa-IR'>Farsi - Iran</option>
                            <option value='fi'>Finnish</option>
                            <option value='fi-FI'>Finnish - Finland</option>
                            <option value='fo'>Faroese</option>
                            <option value='fo-FO'>Faroese - Faroe Islands</option>
                            <option value='fr'>French</option>
                            <option value='fr-BE'>French - Belgium</option>
                            <option value='fr-CA'>French - Canada</option>
                            <option value='fr-CH'>French - Switzerland</option>
                            <option value='fr-FR'>French - France</option>
                            <option value='fr-LU'>French - Luxembourg</option>
                            <option value='fr-MC'>French - Monaco</option>
                            <option value='gl'>Galician</option>
                            <option value='gl-ES'>Galician - Galician</option>
                            <option value='gu'>Gujarati</option>
                            <option value='gu-IN'>Gujarati - India</option>
                            <option value='he'>Hebrew</option>
                            <option value='he-IL'>Hebrew - Israel</option>
                            <option value='hi'>Hindi</option>
                            <option value='hi-IN'>Hindi - India</option>
                            <option value='hr'>Croatian</option>
                            <option value='hr-HR'>Croatian - Croatia</option>
                            <option value='hu'>Hungarian</option>
                            <option value='hu-HU'>Hungarian - Hungary</option>
                            <option value='hy'>Armenian</option>
                            <option value='hy-AM'>Armenian - Armenia</option>
                            <option value='id'>Indonesian</option>
                            <option value='id-ID'>Indonesian - Indonesia</option>
                            <option value='is'>Icelandic</option>
                            <option value='is-IS'>Icelandic - Iceland</option>
                            <option value='it'>Italian</option>
                            <option value='it-CH'>Italian	Italian - Switzerland</option>
                            <option value='it-IT'>Italian - Italy</option>
                            <option value='ja'>Japanese</option>
                            <option value='ja-JP'>Japanese - Japan</option>
                            <option value='ka'>Georgian</option>
                            <option value='ka-GE'>Georgian - Georgia</option>
                            <option value='kk'>Kazakh</option>
                            <option value='kk-KZ'>Kazakh - Kazakhstan</option>
                            <option value='kn'>Kannada</option>
                            <option value='kn-IN'>Kannada - India</option>
                            <option value='ko'>Korean</option>
                            <option value='kok'>Konkani</option>
                            <option value='kok-IN'>Konkani - India</option>
                            <option value='ko-KR'>Korean - Korea</option>
                            <option value='ky'>Kyrgyz</option>
                            <option value='ky-KG'>Kyrgyz - Kyrgyzstan</option>
                            <option value='lt'>Lithuanian</option>
                            <option value='lt-LT'>Lithuanian - Lithuania</option>
                            <option value='lv'>Latvian</option>
                            <option value='lv-LV'>Latvian - Latvia</option>
                            <option value='mk'>Macedonian</option>
                            <option value='mk-MK'>Macedonian - Former Yugoslav Republic of Macedonia</option>
                            <option value='mn'>Mongolian</option>
                            <option value='mn-MN'>Mongolian - Mongolia</option>
                            <option value='mr'>Marathi</option>
                            <option value='mr-IN'>Marathi - India</option>
                            <option value='ms'>Malay</option>
                            <option value='ms-BN'>Malay - Brunei</option>
                            <option value='ms-MY'>Malay - Malaysia</option>
                            <option value='nb-NO'>Norwegian (Bokm?l) - Norway</option>
                            <option value='nl'>Dutch</option>
                            <option value='nl-BE'>Dutch - Belgium</option>
                            <option value='nl-NL'>Dutch - The Netherlands</option>
                            <option value='nn-NO'>Norwegian (Nynorsk) - Norway</option>
                            <option value='no'>Norwegian</option>
                            <option value='pa'>Punjabi</option>
                            <option value='pa-IN'>Punjabi - India</option>
                            <option value='pl'>Polish</option>
                            <option value='pl-PL'>Polish - Poland</option>
                            <option value='pt'>Portuguese</option>
                            <option value='pt-BR'>Portuguese - Brazil</option>
                            <option value='pt-PT'>Portuguese - Portugal</option>
                            <option value='ro'>Romanian</option>
                            <option value='ro-RO'>Romanian - Romania</option>
                            <option value='ru'>Russian</option>
                            <option value='ru-RU'>Russian - Russia</option>
                            <option value='sa'>Sanskrit</option>
                            <option value='sa-IN'>Sanskrit - India</option>
                            <option value='sk'>Slovak</option>
                            <option value='sk-SK'>Slovak - Slovakia</option>
                            <option value='sl'>Slovenian</option>
                            <option value='sl-SI'>Slovenian - Slovenia</option>
                            <option value='sq'>Albanian</option>
                            <option value='sq-AL'>Albanian - Albania</option>
                            <option value='sr-SP-Cyrl'>Serbian (Cyrillic) - Serbia</option>
                            <option value='sr-SP-Latn'>Serbian (Latin) - Serbia</option>
                            <option value='sv'>Swedish</option>
                            <option value='sv-FI'>Swedish - Finland</option>
                            <option value='sv-SE'>Swedish - Sweden</option>
                            <option value='sw'>Swahili</option>
                            <option value='sw-KE'>Swahili - Kenya</option>
                            <option value='syr'>Syriac</option>
                            <option value='syr-SY'>Syriac - Syria</option>
                            <option value='ta'>Tamil</option>
                            <option value='ta-IN'>Tamil - India</option>
                            <option value='te'>Telugu</option>
                            <option value='te-IN'>Telugu - India</option>
                            <option value='th'>Thai</option>
                            <option value='th-TH'>Thai - Thailand</option>
                            <option value='tr'>Turkish</option>
                            <option value='tr-TR'>Turkish - Turkey</option>
                            <option value='tt'>Tatar</option>
                            <option value='tt-RU'>Tatar - Russia</option>
                            <option value='uk'>Ukrainian</option>
                            <option value='uk-UA'>Ukrainian - Ukraine</option>
                            <option value='ur'>Urdu</option>
                            <option value='ur-PK'>Urdu - Pakistan</option>
                            <option value='uz'>Uzbek</option>
                            <option value='uz-UZ-Cyrl'>Uzbek (Cyrillic) - Uzbekistan</option>
                            <option value='uz-UZ-Latn'>Uzbek (Latin) - Uzbekistan</option>
                            <option value='vi'>Vietnamese</option>
                            <option value='zh-CHT'>Chinese (Traditional)</option>
                            <option value='zh-CHS'>Chinese (Simplified)</option>
                            <option value='zh-CN'>Chinese - China</option>
                            <option value='zh-HK'>Chinese - Hong Kong SAR</option>
                            <option value='zh-MO'>Chinese - Macao SAR</option>
                            <option value='zh-SG'>Chinese - Singapore</option>
                            <option value='zh-TW'>Chinese - Taiwan</option>
                        </select>
                    </div>
                    <div class="info-line-form-row">
                        <label for="mux-captions-upload">Subtitle</label>
                        <input id="mux-captions-upload" type="file" accept=".srt,.vtt">
                    </div>
                    <div class="info-line-form-row">
                        <input id="mux-captions-cc" type="checkbox">
                        <label for="mux-captions-cc">Closed Captions</label>
                    </div>
                    <div class="info-line-note">
                        Upload subtitles will not appear in this panel until after mux has processed them.  This may take 1-2 minutes.
                    </div>
                    <div class="info-line-form-buttons">
                        <button type="button" class="button button-primary button-small upload-captions">Upload Captions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
