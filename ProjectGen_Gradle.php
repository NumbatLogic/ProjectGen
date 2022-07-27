<?php

	// GradleSettings & GradleElement are simple gradle formatters to make settings human readable
	class GradleSettings
	{
		private $m_pElementArray;

		public function __construct($pRootElements)
		{
			$this->m_pElementArray = $pRootElements;
		}

		public function AddElement($pGradleElement)
		{
			$this->m_pElementArray[] = $pGradleElement;
		}

		public function ToString()
		{
			$sResult = "";
			for ($i = 0; $i < count($this->m_pElementArray); $i++)
			{
				$sResult .= $this->m_pElementArray[$i]->ToString(0) . "\n";
			}
			return $sResult;
		}
	}

	class GradleElement
	{
		private $m_sName;
		private $m_sType;
		private $m_pValue;

		public function __construct($sType, $sName, $pValue)
		{
			$this->m_sName = $sName;
			$this->m_sType = $sType;
			$this->m_pValue = $pValue;
		}

		public function ToString($iElementDepth)
		{
			$sTabDepth = "";
			for ($i = 0; $i < $iElementDepth; $i++)
			{
				$sTabDepth .= "\t";
			}

			$sResult = $sTabDepth . $this->m_sName;
			if ($this->m_sType == "group")
			{
				$sResult .= " {\n";
				for ($i = 0; $i < count($this->m_pValue); $i++)
				{
					$sResult .= $this->m_pValue[$i]->ToString($iElementDepth + 1) . "\n";
				}
				$sResult .= $sTabDepth . "}";
			}
			else if ($this->m_sType == "function")
			{
				$sResult .= "(" . $this->m_pValue . ")";
			}
			else if ($this->m_sType == "number")
			{
				$sResult .= " " . $this->m_pValue;
			}
			else if ($this->m_sType == "string")
			{
				if (strlen($this->m_pValue) > 0)
				{
					$sResult .= " \"" . $this->m_pValue . "\"";
				}
			}
			else if ($this->m_sType == "stringarray")
			{
				$sResult .= " = [";
				for ($i = 0; $i < count($this->m_pValue); $i++)
				{
					$sResult .= "\"" . $this->m_pValue[$i] . "\", ";
				}
				$sResult .= "]";
			}
			else if ($this->m_sType == "haxstring")
			{
				$sResult .= $this->m_pValue . "\n";
			}
			return $sResult;
		}
	}

	function ProjectGen_Gradle_Output($pSolution, $sAction)
	{
		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);
		$sModuleBaseDirectory = $sBaseDirectory . "/" . $pSolution->GetName() . "/";
		if (!is_dir($sModuleBaseDirectory))
			mkdir($sModuleBaseDirectory);

		$sProjectMakefile = "";
		$sProjectMakefile .= "project(Project)\n\n";
		$sProjectMakefile .= "cmake_minimum_required(VERSION 3.18.1)\n\n";
		$sProjectMakefile .= "add_definitions(-DCMAKE_PLATFORM_ANDROID)\n";
		$sProjectLibraries = "";
		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];

			ProjectGen_Gradle_Project_Output($pSolution, $pProject, $sModuleBaseDirectory);

			if ($pProject->GetKind() == KIND_CONSOLE_APP)
			{
				$sPath = $pProject->GetBaseDirectory() . "/google-services.json";

				if (file_exists($sPath))
					copy($sPath, $sModuleBaseDirectory . "google-services.json");

				$pGradleBuild = new GradleSettings(
					array(
						// HAX_BB hardcode using zero length string element
						new GradleElement("string", "apply plugin: 'com.android.application'", ""),
						new GradleElement("group", "android",
							array(
								new GradleElement("number", "compileSdkVersion", 31),
								//new GradleElement("string", "buildToolsVersion", "30.0.3"),
								new GradleElement("string", "ndkVersion", "21.4.7075529"),
								new GradleElement("group", "defaultConfig",
									array(
										new GradleElement("string", "applicationId", $pProject->GetBundleIdentifier()),
										new GradleElement("number", "minSdkVersion", 15),
										new GradleElement("number", "targetSdkVersion", 31),
										new GradleElement("number", "versionCode", 1),
										new GradleElement("string", "versionName", "1.0"),
										new GradleElement("group", "ndk", array(new GradleElement("string", "abiFilters \"arm64-v8a\", \"armeabi-v7a\", \"x86\", \"x86_64\"", ""))), // , \"mips\" 
										new GradleElement("group", "externalNativeBuild",
											array(
												new GradleElement("group", "cmake",
													array(
														new GradleElement("string", "cFlags", "-Wno-incompatible-pointer-types"),
													)
												)
											)
										),
									)
								),
								new GradleElement("group", "sourceSets",
									array(
										new GradleElement("group", "main",
											array(
												new GradleElement("string", "manifest.srcFile",
													"AndroidManifest.xml"),
												new GradleElement("stringarray", "res.srcDirs",
													array(
														"res"
														)
													),
												new GradleElement("stringarray", "java.srcDirs",
													array(
														"../../../Engine/Platform/Android/Java"
														)
													),
												new GradleElement("stringarray", "assets.srcDirs",
													array(
														"../../../../Assets"
														)
													)
												)
											)
										)
									),
								new GradleElement("group", "buildTypes",
									array(
										new GradleElement("group", "release",
											array(
												new GradleElement("bool", "minifyEnabled", false),
												new GradleElement("string",
													"proguardFiles getDefaultProguardFile('proguard-android.txt'), 'proguard-rules.pro'", "")
												)
											)
										)
									),
								new GradleElement("group", "externalNativeBuild",
									array(
										new GradleElement("group", "cmake",
											array( 	
												//new GradleElement("string", "cFlags", "-DCMAKE_PLATFORM_ANDROID"),
												new GradleElement("string", "path", "CMakeLists.txt"),
												new GradleElement("string", "version", "3.18.1+"),
												//version "3.7.1"
												)
											)
										)
									),
								)
							),
						new GradleElement("group", "dependencies",
							array(
								//new GradleElement("string", "compile fileTree(include: ['*.jar'], dir: 'libs')", ""),
								//new GradleElement("string", "implementation 'com.android.support:appcompat-v7:24.2.1'", ""),
					// for ads			new GradleElement("string", "implementation 'com.google.firebase:firebase-ads:20.4.0'", "")

								)
							),
						new GradleElement("string", "apply plugin: 'com.google.gms.google-services'", ""),
						)
					);
				file_put_contents($sModuleBaseDirectory . "/build.gradle", $pGradleBuild->ToString());
				file_put_contents($sModuleBaseDirectory . "/proguard-rules.pro", "");


				// AndroidManifest.xml
				$pXmlFile = new DOMDocument('1.0', 'UTF-8');
				$pXmlFile->formatOutput = true;

				$pManifest = $pXmlFile->appendChild($pXmlFile->createElement("manifest"));
					$pManifest->setAttribute("xmlns:android", "http://schemas.android.com/apk/res/android");
					$pManifest->setAttribute("package", $pProject->GetBundleIdentifier());

					$pElement = $pManifest->appendChild($pXmlFile->createElement("uses-feature"));
						$pElement->setAttribute("android:glEsVersion", "0x00020000");
						$pElement->setAttribute("android:required", "true");

					$pElement = $pManifest->appendChild($pXmlFile->createElement("uses-permission"));
						$pElement->setAttribute("android:name", "android.permission.INTERNET");

					$pApplication = $pManifest->appendChild($pXmlFile->createElement("application"));
						$pApplication->setAttribute("android:icon", "@mipmap/ic_launcher");
						$pApplication->setAttribute("android:label", "@string/app_name");

						$pActivity = $pApplication->appendChild($pXmlFile->createElement("activity"));
							$pActivity->setAttribute("android:name", "com.numbatlogic.ClientActivity");
							$pActivity->setAttribute("android:theme", "@android:style/Theme.NoTitleBar.Fullscreen");
							$pActivity->setAttribute("android:launchMode", "singleTask");
							$pActivity->setAttribute("android:configChanges", "orientation|screenSize|keyboardHidden");
							$pActivity->setAttribute("android:exported", "true");

							$pElement = $pActivity->appendChild($pXmlFile->createElement("meta-data"));
								$pElement->setAttribute("android:name", "android.app.lib_name");
								$pElement->setAttribute("android:value", $pProject->GetName());

							$pIntentFilter = $pActivity->appendChild($pXmlFile->createElement("intent-filter"));

								$pElement = $pIntentFilter->appendChild($pXmlFile->createElement("action"));
									$pElement->setAttribute("android:name", "android.intent.action.MAIN");

								$pElement = $pIntentFilter->appendChild($pXmlFile->createElement("category"));
									$pElement->setAttribute("android:name", "android.intent.category.LAUNCHER");

				file_put_contents($sModuleBaseDirectory . "AndroidManifest.xml", $pXmlFile->saveXML());


				// res
				$sFolder = $sModuleBaseDirectory . "res/";
				if (!is_dir($sFolder))
					mkdir($sFolder);

					// drawable
					$sFolder = $sModuleBaseDirectory . "res/drawable/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

					// mipmap-hdpi
					$sFolder = $sModuleBaseDirectory . "res/mipmap-hdpi/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						if (!ProjectGen_SvgRender($pProject->GetIcon(), $pProject->GetIconMask(), $sFolder . "ic_launcher.png", 72))
							echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sFolder . "ic_launcher.png\n";

					// mipmap-mdpi
					$sFolder = $sModuleBaseDirectory . "res/mipmap-mdpi/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						if (!ProjectGen_SvgRender($pProject->GetIcon(), $pProject->GetIconMask(), $sFolder . "ic_launcher.png", 48))
							echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sFolder . "ic_launcher.png\n";

					// mipmap-xhdpi
					$sFolder = $sModuleBaseDirectory . "res/mipmap-xhdpi/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						if (!ProjectGen_SvgRender($pProject->GetIcon(), $pProject->GetIconMask(), $sFolder . "ic_launcher.png", 96))
							echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sFolder . "ic_launcher.png\n";

					// mipmap-xxhdpi
					$sFolder = $sModuleBaseDirectory . "res/mipmap-xxhdpi/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						if (!ProjectGen_SvgRender($pProject->GetIcon(), $pProject->GetIconMask(), $sFolder . "ic_launcher.png", 144))
							echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sFolder . "ic_launcher.png\n";

					// mipmap-xxxhdpi
					$sFolder = $sModuleBaseDirectory . "res/mipmap-xxxhdpi/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						if (!ProjectGen_SvgRender($pProject->GetIcon(), $pProject->GetIconMask(), $sFolder . "ic_launcher.png", 192))
							echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sFolder . "ic_launcher.png\n";

					// values
					$sFolder = $sModuleBaseDirectory . "res/values/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						// colors.xml
						$pXmlFile = new DOMDocument('1.0', 'UTF-8');
						$pResources = $pXmlFile->appendChild($pXmlFile->createElement("resources"));
					
							$pElement = $pResources->appendChild($pXmlFile->createElement("color"));
								$pElement->setAttribute("name", "colorPrimary");
								$pElement->appendChild($pXmlFile->createTextNode("#3F51B5"));

							$pElement = $pResources->appendChild($pXmlFile->createElement("color"));
								$pElement->setAttribute("name", "colorPrimaryDark");
								$pElement->appendChild($pXmlFile->createTextNode("#303F9F"));

							$pElement = $pResources->appendChild($pXmlFile->createElement("color"));
								$pElement->setAttribute("name", "colorAccent");
								$pElement->appendChild($pXmlFile->createTextNode("#FF4081"));

						file_put_contents($sFolder . "colors.xml", $pXmlFile->saveXML());


						// dimens.xml
						// <!-- Default screen margins, per the Android Design guidelines. -->
						$pXmlFile = new DOMDocument('1.0', 'UTF-8');
						$pResources = $pXmlFile->appendChild($pXmlFile->createElement("resources"));
					
							$pElement = $pResources->appendChild($pXmlFile->createElement("dimen"));
								$pElement->setAttribute("name", "activity_horizontal_margin");
								$pElement->appendChild($pXmlFile->createTextNode("16dp"));

							$pElement = $pResources->appendChild($pXmlFile->createElement("dimen"));
								$pElement->setAttribute("name", "activity_vertical_margin");
								$pElement->appendChild($pXmlFile->createTextNode("16dp"));

						file_put_contents($sFolder . "dimens.xml", $pXmlFile->saveXML());


						// strings.xml
						$pXmlFile = new DOMDocument('1.0', 'UTF-8');
						$pResources = $pXmlFile->appendChild($pXmlFile->createElement("resources"));
					
							$pElement = $pResources->appendChild($pXmlFile->createElement("string"));
								$pElement->setAttribute("name", "app_name");
								$pElement->appendChild($pXmlFile->createTextNode($pProject->GetFriendlyName()));

						file_put_contents($sFolder . "strings.xml", $pXmlFile->saveXML());


					// values-w820dp
					$sFolder = $sModuleBaseDirectory . "res/values-w820dp/";
					if (!is_dir($sFolder))
						mkdir($sFolder);

						// dimens.xml
						//  <!-- Example customization of dimensions originally defined in res/values/dimens.xml
						//	(such as screen margins) for screens with more than 820dp of available width. This
						//	would include 7" and 10" devices in landscape (~960dp and ~1280dp respectively). -->
						$pXmlFile = new DOMDocument('1.0', 'UTF-8');
						$pResources = $pXmlFile->appendChild($pXmlFile->createElement("resources"));
					
							$pElement = $pResources->appendChild($pXmlFile->createElement("dimen"));
								$pElement->setAttribute("name", "activity_horizontal_margin");
								$pElement->appendChild($pXmlFile->createTextNode("64dp"));

						file_put_contents($sFolder . "dimens.xml", $pXmlFile->saveXML());
			}

			$sProjectMakefile .= "include_directories(\${CMAKE_CURRENT_SOURCE_DIR}/" . $pProject->GetName() . "/)\n";
			$sProjectMakefile .= "include(\${CMAKE_CURRENT_SOURCE_DIR}/" . $pProject->GetName() . "/CMakeLists.txt" . ")\n";
			$sProjectLibraries .= "\t" . $pProject->GetName() . "\n";
		}

		$sProjectMakefile .= "\n";

		{
			$sProjectMakefile .= "include_directories(\${ANDROID_NDK}/sources/android/native_app_glue)\n";
		}

		file_put_contents($sModuleBaseDirectory . "/CMakeLists.txt", $sProjectMakefile);

		{
			$pRootGradleBuild = new GradleSettings(
				array(
					new GradleElement("group", "buildscript",
						array (
							new GradleElement("group", "repositories",
								array(
									new GradleElement("function", "mavenCentral", ""),
									new GradleElement("group", "maven",
										array(
											new GradleElement("string", "url", "https://maven.google.com"),
										)
									)
								)
							),
							new GradleElement("group", "dependencies",
								array(
									new GradleElement("string", "classpath", "com.android.tools.build:gradle:7.0.2"), // match gradle-wrapper below!
									new GradleElement("string", "classpath", "com.google.gms:google-services:4.3.10")
								)
							)
						)
					),
					new GradleElement("group", "allprojects",
						array (
							new GradleElement("haxstring", "", "if (org.gradle.internal.os.OperatingSystem.current().isWindows()) { buildDir = \"C:/tmp/\${rootProject.name}/\${project.name}\" }"),
							new GradleElement("group", "repositories",
								array(
									new GradleElement("function", "mavenCentral", ""),
									new GradleElement("group", "maven",
										array(
											new GradleElement("string", "url", "https://maven.google.com"),
										)
									)
								)
							),
						)
					)
				)
			);
			file_put_contents($sBaseDirectory . "/build.gradle", $pRootGradleBuild->ToString());
		}
		{
			$pRootGradleSettings = new GradleSettings(
				array(
					new GradleElement("string", "include", ":" . $pSolution->GetName())
					)
				);
			file_put_contents($sBaseDirectory . "/settings.gradle", $pRootGradleSettings->ToString());
		}

		{
			file_put_contents($sBaseDirectory . "/gradle.properties", "android.useAndroidX=true");
		}

		{
			if (!is_dir($sBaseDirectory . "/gradle"))
				mkdir($sBaseDirectory . "/gradle");

			if (!is_dir($sBaseDirectory . "/gradle/wrapper"))
				mkdir($sBaseDirectory . "/gradle/wrapper");

			file_put_contents($sBaseDirectory . "/gradle/wrapper/gradle-wrapper.properties", "distributionUrl=https\\://services.gradle.org/distributions/gradle-7.0.2-all.zip");
		}
	}
	
	// function ProjectGen_FlattenFileArray($xFileArray, $sPath)
	function ProjectGen_Gradle_Project_Output($pSolution, $pProject, $sBaseDirectory)
	{
		$sOutput = "";


	//$sFileArray = ProjectGen_FlattenFileArray($pProject->m_xFileArray, "");

		$xFileArray = $pProject->m_xFileArray;
		$xSourceFileArray = array();
		ProjectGen_Gradle_Recurse_Source_Files($xFileArray, $xSourceFileArray);
		$sSources = "";
		foreach($xSourceFileArray as $xFile)
		{
			$sSourcePath = ProjectGen_GetRelativePath(realpath($sBaseDirectory), realpath($xFile));
			//$sSources .= "\t" . $sSourcePath . " z " . realpath($sBaseDirectory) . " x " . $sBaseDirectory . " c " . realpath($xFile) . " v " . $xFile . "\n";
			$sSources .= "\t\${CMAKE_CURRENT_SOURCE_DIR}/" . $sSourcePath . "\n";
		}

		$sConfigurationArray = array(
			CONFIGURATION_DEBUG,
			CONFIGURATION_RELEASE,
		);
		$sArchitectureArray = array(
			//"Win32", "x64"
			ARCHITECTURE_32,
			ARCHITECTURE_64,
		);
		$sIncludes = "";
		foreach ($sConfigurationArray as $sConfiguration)
		{
			foreach ($sArchitectureArray as $sArchitecture)
			{
				if ($sConfiguration == CONFIGURATION_DEBUG && $sArchitecture == ARCHITECTURE_32)// HAX_BB
				{
					$sIncludeDirectoryArray = $pProject->GetIncludeDirectoryArray($sConfiguration, $sArchitecture);
					for ($j = 0; $j < count($sIncludeDirectoryArray); $j++)
					{
						$sInclude = ProjectGen_GetRelativePath(
							realpath($sBaseDirectory),
							realpath($pProject->GetBaseDirectory() . "/" . $sIncludeDirectoryArray[$j])
							);
						$sIncludes .= "\t " . "\${CMAKE_CURRENT_SOURCE_DIR}/" . $sInclude . "\n";
					}
				}
			}
		}
		if (strlen($sIncludes) > 0)
		{
			$sOutput .=
				"include_directories(\n"
				. $sIncludes
				. "\t)\n";
		}
		{
			$sOutput .=
				"add_library(\n"
				. "\t" . $pProject->GetName() . "\n"
				. "\t" . ($pProject->GetKind() == KIND_CONSOLE_APP ? "SHARED" : "STATIC") . "\n"
				. $sSources
				. "\t)\n";
		}

		{
			$sProjectLibraries = "\t" . $pProject->GetName() . "\n";

			// HAX_BB android sdk libs
			if ($pProject->GetName() == "NewClient" || $pProject->GetName() == "Engine")
			{
				$sProjectLibraries .= "\tGLESv2\n";
				$sProjectLibraries .= "\tEGL\n";
				$sProjectLibraries .= "\tOpenSLES\n";
				$sProjectLibraries .= "\tlog\n";
				$sProjectLibraries .= "\tandroid\n";
				$sProjectLibraries .= "\tz\n";
			}

			$sDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);
			for ($j = 0; $j < count($sDependancyArray); $j++)
			{
				$sDependancy = $sDependancyArray[$j];
				$pDependancy = $pSolution->GetProjectByName($sDependancy);
				if ($pDependancy)
				{
					$sProjectLibraries .= "\t" . $sDependancy . "\n";
				}
				else
				{
					$sProjectLibraries .= "\t" . "\${CMAKE_CURRENT_SOURCE_DIR}/" . $sDependancy . "\n";
				}
			}
			$sOutput .=
				"target_link_libraries(" . "\n"
				. $sProjectLibraries . ")\n";
		}

		if (!is_dir($sBaseDirectory . "/" . $pProject->GetName()))
			mkdir($sBaseDirectory . "/" . $pProject->GetName());
		file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . "/CMakeLists.txt", $sOutput);
	}

	function ProjectGen_Gradle_Recurse_Source_Files($xFileArray, &$xOutputFileArray)
	{
		foreach ($xFileArray  as $xFile)
		{
			switch ($xFile["sType"])
			{
				case FILE_TYPE_DIRECTORY:
				{
					ProjectGen_Gradle_Recurse_Source_Files($xFile["xFileArray"], $xOutputFileArray);
					break;
				}
				case FILE_TYPE_FILE:
				{
					if ($xFile["sExtension"] == "c" || $xFile["sExtension"] == "cpp")
					{
						$xOutputFileArray[] = &$xFile["sPath"];
					}
					break;
				}
				default: throw new Exception("Oh hai! It's borked!");
			}
		}
	}
?>
