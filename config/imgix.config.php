<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

if (!defined('ABSPATH')) { header('Location: /'); die; }

return [
    "name" => "Imgix",
	"title" => "Imgix Support",
	"description" => "Serves images through imgix.com",
	"class" => "ILAB\\MediaCloud\\Tools\\Imgix\\ImgixTool",
	"env" => "ILAB_MEDIA_IMGIX_ENABLED",
	"dependencies" => [
		"crop",
		"storage"
	],
	"helpers" => [
		"ilab-imgix-helpers.php"
	],
    "incompatiblePlugins" => [
        "Smush" => [
            "plugin" => "wp-smushit/wp-smush.php",
            "description" => "The free version of this plugin does not optimize the main image, only thumbnails.  When the Imgix tool is enabled, thumbnails are not generated - therefore this plugin isn't any use.  The Pro (paid) version of this plugin DOES optimize the main image though."
        ],
    ],
    "badPlugins" => [
        "BuddyPress" => [
            "plugin" => "buddypress/bp-loader.php",
            "description" => "Uploading profile or cover images results in broken images."
        ]
    ],
	"settings" => [
		"title" => "Imgix Settings",
		"menu" => "Imgix Settings",
		"options-page" => "media-tools-imgix",
		"options-group" => "ilab-media-imgix",
		"groups" => [
			"ilab-media-imgix-settings" => [
				"title" => "Imgix Settings",
				"description" => "Required settings for imgix integration to work.",
				"options" => [
					"ilab-media-imgix-domains" => [
						"title" => "Imgix Domains",
						"description" => "List of your source domains.  For more information, please read the <a href='https://www.imgix.com/docs/tutorials/creating-sources' target='_blank'>imgix documentation</a>",
						"type" => "text-area"
					],
					"ilab-media-imgix-signing-key" => [
						"title" => "Imgix Signing Key",
						"description" => "Optional signing key to create secure URLs.  <strong>Recommended</strong>.  For information on setting it up, refer to the <a href='https://www.imgix.com/docs/tutorials/securing-images' target='_blank'>imgix documentation</a>.",
						"type" => "password"
					],
					"ilab-media-imgix-use-https" => [
						"title" => "Use HTTPS",
						"description" => "Use HTTPS for image URLs",
						"type" => "checkbox"
					]
				]
			],
			"ilab-media-imgix-image-settings" => [
				"title" => "Imgix Image Settings",
				"description" => "Put your imgix image settings here",
				"options" => [
					"ilab-media-imgix-default-quality" => [
						"title" => "Lossy Image Quality",
						"type" => "number"
					],
					"ilab-media-imgix-auto-format" => [
						"title" => "Auto Format",
						"description" => "Allows imgix to choose the most appropriate file format to deliver your image based on the requesting web browser.",
						"type" => "checkbox"
					],
					"ilab-media-imgix-auto-compress" => [
						"title" => "Auto Compress",
						"description" => "Allows imgix to automatically compress your images.",
						"type" => "checkbox"
					],
                    "ilab-media-imgix-enable-alt-formats" => [
                        "title" => "Enable Alternative Formats",
                        "description" => "Allow uploads of Photoshop PSDs, TIFF images and Adobe Illustrator documents.  Note that if you enable this, you'll only be able to view them as images on your site while Imgix is enabled.  Basically, once you head down this path, you cannot go back.",
                        "type" => "checkbox"
                    ],
                    "ilab-media-imgix-generate-thumbnails" => [
                        "title" => "Keep WordPress Thumbnails",
                        "description" => "Because Imgix can dynamically create new sizes for existing images, having WordPress create thumbnails is potentially pointless, a probable waste of space and definitely slows down uploads.  However, if you plan to stop using Imgix, having those thumbnails on S3 or locally will save you having to regenerate thumbnails later.  <strong>IMPORTANT:</strong> Thumbnails will not be generated when you perform a direct upload because those uploads are sent directly to S3 without going through your WordPress server.",
                        "type" => "checkbox",
                        "default" => "true"
                    ],
					"ilab-media-imgix-render-pdf-files" => [
						"title" => "Render PDF Files",
						"description" => "Render PDF files as images.  Like the <em>Enable Alternative Formats</em>, once you enable this option, you'll only be able to see the PDFs as images while Imgix is enabled.",
						"type" => "checkbox"
					],
					"ilab-media-imgix-detect-faces" => [
						"title" => "Detect Faces",
						"description" => "After each upload Media Cloud will use Imgix's face detection API to detect faces in the image.  This can be used with Focus Crop in the image editor, or on the front-end however you choose.  <strong>Note:</strong> If you are relying on this functionality, the better option would be to use the <a href='admin.php?page=media-tools-rekognition'>Rekognition</a> tool.  It is more accurate with less false positives.  If Rekognition is enabled, this setting is ignored in favor of Rekognition's results.",
						"type" => "checkbox",
						"default" => false
					]
				]
			],
			"ilab-media-imgix-gif-settings" => [
				"title" => "Imgix GIF Settings",
				"description" => "Controls how animated gifs appear on the site.",
				"options" => [
                    "ilab-media-imgix-enable-gifs" => [
                        "title" => "Enable GIFs",
                        "description" => "Enables support for animated GIFs.  If this is not enabled, any uploaded GIFs will be converted.  <strong>Note that this is a feature of premium Imgix accounts only.  GIF support is not enabled on standard Imgix accounts by default.  Contact Imgix sales for more information.</strong>",
                        "type" => "checkbox",
                        "default" => false
                    ],
                    "ilab-media-imgix-skip-gifs" => [
                        "title" => "Serve GIFs from Storage",
                        "description" => "If this option is enabled, GIFs will be served straight from S3, or whatever storage provider you are using, and not from Imgix.  If <strong>Enable GIFs</strong> is enabled, this setting is ignored.",
                        "type" => "checkbox",
                        "default" => false
                    ],
					"ilab-media-imgix-no-gif-sizes" => [
						"title" => "Disallow Animated GIFs for Sizes",
						"description" => "List the sizes that aren't allowed to have animated GIFs.  These sizes will display jpegs instead.",
						"type" => "text-area"
					]
				]
			]
		],
		"params" => [
			"adjust" => [
				"--Auto" => [
					"auto" => [
						"type" => "pillbox",
						"options" => [
							"enhance" => [
								"title" => "Auto Enhance",
								"default" => 0
							],
							"redeye" => [
								"title" => "Remove Red Eye",
								"default" => 0
							]
						],
						"selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
							if (isset($settings['auto'])) {
								$parts=explode(',',$settings['auto']);
								foreach($parts as $part) {
									if ($part==$currentValue) {
										return $selectedOutput;
									}
								}
							}

							return $unselectedOutput;
						}
					]
				],
                "Flip" => [
                    "flip" => [
                        "type" => "pillbox",
                        "options" => [
                            "h" => [
                                "title" => "Horizontal",
                                "default" => 0
                            ],
                            "v" => [
                                "title" => "Vertical",
                                "default" => 0
                            ]
                        ],
                        "selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
                            if (isset($settings['flip'])) {
                                $parts=explode(',',$settings['flip']);
                                foreach($parts as $part) {
                                    if ($part==$currentValue) {
                                        return $selectedOutput;
                                    }
                                }
                            }

                            return $unselectedOutput;
                        }
                    ]
                ],
				"Luminosity Controls" => [
					"bri" => [
						"title" => "Brightness",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"con" => [
						"title" => "Contrast",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"exp" => [
						"title" => "Exposure",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"gam" => [
						"title" => "Gamma",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"high" => [
						"title" => "Highlight",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"shad" => [
						"title" => "Shadow",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					]
				],
				"Color Controls" => [
					"hue" => [
						"title" => "Hue",
						"type" => "slider",
						"min" => -359,
						"max" => 359,
						"default" => 0
					],
					"sat" => [
						"title" => "Saturation",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"vib" => [
						"title" => "Vibrancy",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					]
				],
				"Noise/Sharpen/Blur" => [
					"sharp" => [
						"title" => "Sharpen",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					],
					"nr" => [
						"title" => "Noise Reduction",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"nrs" => [
						"title" => "Noise Reduction Sharpen Bound",
						"type" => "slider",
						"min" => -100,
						"max" => 100,
						"default" => 0
					],
					"blur" => [
						"title" => "Blur",
						"type" => "slider",
						"min" => 0,
						"max" => 2000,
						"default" => 0
					]
				],
				"Transform" => [
					"rot" => [
						"title" => "Rotation",
						"type" => "slider",
						"min" => -359,
						"max" => 359,
						"default" => 0
					]
				]
			],
			"stylize" => [
				"Stylize" => [
					"blend" => [
						"title" => "Tint",
						"type" => "blend-color",
						"blend-param" => "bm",
						"blends" => [
							"none" => "Normal",
							"color" => "Color",
							"burn" => "Burn",
							"dodge" => "Dodge",
							"darken" => "Darken",
							"difference" => "Difference",
							"exclusion" => "Exclusion",
							"hardlight" => "Hard Light",
							"hue" => "Hue",
							"lighten" => "Lighten",
							"luminosity" => "Luminosity",
							"multiply" => "Multiply",
							"overlay" => "Overlay",
							"saturation" => "Saturation",
							"screen" => "Screen",
							"softlight" => "Soft Light"
						]
					],
					"htn" => [
						"title" => "Halftone",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					],
					"px" => [
						"title" => "Pixellate",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					],
					"mono" => [
						"title" => "Monochrome",
						"type" => "color"
					],
					"sepia" => [
						"title" => "Sepia",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					]
				],
				"Border" => [
					"border-color" => [
						"title" => "Border Color",
						"type" => "color"
					],
					"border-width" => [
						"title" => "Border Width",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					]
				],
				"Padding" => [
					"padding-color" => [
						"title" => "Padding Color",
						"type" => "color"
					],
					"padding-width" => [
						"title" => "Padding Width",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					]
				]
			],
			"watermark" => [
				"Watermark Media" => [
					"media" => [
						"title" => "Watermark Image",
						"type" => "media-chooser",
						"imgix-param" => "mark",
						"dependents" => [
							"markalign",
							"markalpha",
							"markpad",
							"markscale"
						]
					]
				],
				"Watermark Settings" => [
					"markalign" => [
						"title" => "Watermark Alignment",
						"type" => "alignment"
					],
					"markalpha" => [
						"title" => "Watermark Alpha",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 100
					],
					"markpad" => [
						"title" => "Watermark Padding",
						"type" => "slider",
						"min" => 0,
						"max" => 100,
						"default" => 0
					],
					"markscale" => [
						"title" => "Watermark Scale",
						"type" => "slider",
						"min" => 0,
						"max" => 200,
						"default" => 100
					]
				]
			],
			"focus-crop" => [
				"--Focus" => [
					"focalpoint" => [
						"type" => "pillbox",
						"exclusive" => true,
						"options" => [
							"focalpoint" => [
								"title" => "Focal Point",
								"default" => 0
							],
							"usefaces" => [
								"title" => "Use Faces",
								"default" => 0
							],
							"entropy" => [
								"title" => "Entropy",
								"default" => 0
							],
							"edges" => [
								"title" => "Edges",
								"default" => 0
							]
						],
						"selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
							if (isset($settings['focalpoint']) && ($settings['focalpoint'] == $currentValue)) {
								return $selectedOutput;
							}

							return $unselectedOutput;
						}
					]
				],
				"Focal Point" => [
					"fp-z" => [
						"title" => "Focal Point Zoom",
						"type" => "slider",
						"min" => 0,
						"max" => 5,
						"default" => 1
					]
				],
				"Faces" => [
					"faceindex" => [
						"title" => "Face Index",
						"type" => "slider",
						"min" => 0,
						"max" => 5,
						"default" => 0
					]
				]
			]
		]
	]
];