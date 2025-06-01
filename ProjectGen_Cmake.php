<?php
	function ProjectGen_Cmake_Output($pSolution, $sAction)
	{
		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);
		$sModuleBaseDirectory = $sBaseDirectory /*. "/" . $pSolution->GetName()*/ . "/";
		if (!is_dir($sModuleBaseDirectory))
			mkdir($sModuleBaseDirectory);

		$sProjectMakefile = "";
		$sProjectMakefile .= "project(" . $pSolution->GetName() . ")\n\n";
		$sProjectMakefile .= "cmake_minimum_required(VERSION 3.13.4)\n\n";
		$sProjectMakefile .= "set(CMAKE_POSITION_INDEPENDENT_CODE ON)\n\n";
		$sProjectMakefile .= "set(CMAKE_CXX_FLAGS  \"\${CMAKE_CXX_FLAGS} -Wall -Wextra -Werror -Wno-unused-parameter -Wno-logical-op-parentheses -Wno-unused-variable -Wno-dangling-else -Wno-switch -Wno-unused-but-set-variable\")\n\n";

		//-DCMAKE_CXX_FLAGS="-fPIC"
		//$sProjectMakefile .= "add_definitions(-DCMAKE_PLATFORM_ANDROID)\n";
		$sProjectLibraries = "";
		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];

			ProjectGen_Cmake_Project_Output($pSolution, $pProject, $sModuleBaseDirectory, $sAction);

			

			$sProjectMakefile .= "include_directories(\${CMAKE_CURRENT_SOURCE_DIR}/" . $pProject->GetName() . "/)\n";
			$sProjectMakefile .= "include(\${CMAKE_CURRENT_SOURCE_DIR}/" . $pProject->GetName() . "/CMakeLists.txt" . ")\n";
			$sProjectLibraries .= "\t" . $pProject->GetName() . "\n";
		}

		$sProjectMakefile .= "\n";

		{
			//$sProjectMakefile .= "include_directories(\${ANDROID_NDK}/sources/android/native_app_glue)\n";
		}

		file_put_contents($sModuleBaseDirectory . "/CMakeLists.txt", $sProjectMakefile);
	}

	function ProjectGen_Cmake_Project_Output($pSolution, $pProject, $sBaseDirectory, $sAction)
	{
		$sOutput = "";

		$sDefineArray = $pSolution->GetDefineArray($sAction);

	//$sFileArray = ProjectGen_FlattenFileArray($pProject->m_xFileArray, "");

		$xFileArray = $pProject->m_xFileArray;
		$xSourceFileArray = array();
		ProjectGen_Cmake_Recurse_Source_Files($xFileArray, $xSourceFileArray);
		$sSources = "";
		foreach($xSourceFileArray as $xFile)
		{
			$sSourcePath = ProjectGen_GetRelativePath(realpath($sBaseDirectory), realpath($xFile));
			//$sSources .= "\t" . $sSourcePath . " z " . realpath($sBaseDirectory) . " x " . $sBaseDirectory . " c " . realpath($xFile) . " v " . $xFile . "\n";
			$sSources .= "\t" . $sSourcePath . "\n";
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

		$sOutput .= "add_compile_definitions(NB_DEBUG)\n";
		$sOutput .= "add_compile_definitions(DEBUG)\n";

		if (count($sDefineArray) > 0)
			for ($j = 0; $j < count($sDefineArray); $j++)
				$sOutput .= "add_compile_definitions(" . $sDefineArray[$j] . ")\n";

		$sBuildOptions = implode(" ", $pProject->GetBuildOptionArray($sConfiguration, $sArchitecture));

		if (strlen($sBuildOptions) > 0)
			$sOutput .= "add_compile_options(" . $sBuildOptions . ")\n";

		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{
			$sOutput .=
				"add_executable(\n"
				. "\t" . $pProject->GetName() . "\n"
				. $sSources
				. "\t)\n";
		}
		else
		{
			$sOutput .=
				"add_library(\n"
				. "\t" . $pProject->GetName() . "\n"
				. "\t" . "STATIC" . "\n"
				. $sSources
				. "\t)\n";
		}

		{
			$sProjectLibraries = "\t" . $pProject->GetName() . "\n";

			// HAX_BB android sdk libs
			if ($pProject->GetName() == "NewClient" || $pProject->GetName() == "Engine")
			{
				/*$sProjectLibraries .= "\tGLESv2\n";
				$sProjectLibraries .= "\tEGL\n";
				$sProjectLibraries .= "\tOpenSLES\n";
				$sProjectLibraries .= "\tlog\n";
				$sProjectLibraries .= "\tandroid\n";
				$sProjectLibraries .= "\tz\n";*/
			}

			$sDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);
			for ($j = 0; $j < count($sDependancyArray); $j++)
			{
				$sDependancy = $sDependancyArray[$j];
				$pDependancy = $pSolution->GetProjectByName($sDependancy);
				$sProjectLibraries .= "\t" . $sDependancy . "\n";
			}
			$sOutput .=
				"target_link_libraries(" . "\n"
				. $sProjectLibraries . ")\n";
		}

		if (!is_dir($sBaseDirectory . "/" . $pProject->GetName()))
			mkdir($sBaseDirectory . "/" . $pProject->GetName());
		file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . "/CMakeLists.txt", $sOutput);
	}

	function ProjectGen_Cmake_Recurse_Source_Files($xFileArray, &$xOutputFileArray)
	{
		foreach ($xFileArray  as $xFile)
		{
			switch ($xFile["sType"])
			{
				case FILE_TYPE_DIRECTORY:
				{
					ProjectGen_Cmake_Recurse_Source_Files($xFile["xFileArray"], $xOutputFileArray);
					break;
				}
				case FILE_TYPE_FILE:
				{
					if ($xFile["sExtension"] == "c" || $xFile["sExtension"] == "cpp" || $xFile["sExtension"] == "h")
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
