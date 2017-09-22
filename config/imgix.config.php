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
					"ilab-media-imgix-render-pdf-files" => [
						"title" => "Render PDF Files",
						"description" => "Render PDF files as images.  Like the <em>Enable Alternative Formats</em>, once you enable this option, you'll only be able to see the PDFs as images while Imgix is enabled.",
						"type" => "checkbox"
					]
				]
			],
			"ilab-media-imgix-gif-settings" => [
				"title" => "Imgix GIF Settings",
				"description" => "Controls how animated gifs appear on the site.",
				"options" => [
					"ilab-media-imgix-enable-gifs" => [
						"title" => "Enable GIFs",
						"description" => "Enables support for animated GIFs.  If this is not enabled, any uploaded GIFs will be converted.",
						"type" => "checkbox"
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
						]
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
			]
		]
	]
];