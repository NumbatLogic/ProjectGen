<?php

	function ProjectGen_Esp_Idf_Output($pSolution, $sAction)
	{
		global $g_sConfigurationArray;
		global $g_sArchitectureArray;

		$pMainProject = null;

		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);

		$sDefineArray = $pSolution->GetDefineArray($sAction);

		$sProjectDirectoryArray = array();

		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);

		//if (!is_dir($sBaseDirectory . "/components"))
		//	mkdir($sBaseDirectory . "/components");

		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			$sProjectDirectory = $sBaseDirectory . "/" . /*components/" .*/ $pProject->GetName();
			
			if ($pProject->GetKind() == KIND_CONSOLE_APP)
			{
				if ($pMainProject != null)
					throw new Exception('Can only have one console app');

				$pMainProject = $pProject;
				$sProjectDirectory = $sBaseDirectory . "/main";
			}

			$sProjectDirectoryArray[] = $sProjectDirectory;
			if (!is_dir($sProjectDirectory))
				mkdir($sProjectDirectory);
		}



		$sOutput = "";
		$sOutput .= "# The following lines of boilerplate have to be in your project's\n";
		$sOutput .= "# CMakeLists in this exact order for cmake to work correctly\n";
		$sOutput .= "cmake_minimum_required(VERSION 3.5)\n";
		$sOutput .= "\n";
		$sOutput .= "set(EXTRA_COMPONENT_DIRS";
			for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
			{
				$pProject = $pSolution->m_pProjectArray[$i];

				if ($pProject == $pMainProject)
					$sOutput .= " \"main\"";
				else
					$sOutput .= " \"" . $pProject->GetName() . "\"";
				
			}
		$sOutput .= ")\n";

		$sOutput .= "include(\$ENV{IDF_PATH}/tools/cmake/project.cmake)\n";
		$sOutput .= "project(" . $pSolution->GetName() . ")\n";
		file_put_contents($sBaseDirectory . "/CMakeLists.txt", $sOutput);






		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			$sProjectDirectory = $sProjectDirectoryArray[$i];

			//if ($pProject == $pMainProject)
			{
				$sOutput = "";
				$sOutput .= "idf_component_register(SRCS";
					$sFileArray = ProjectGen_FlattenFileArray($pProject->m_xFileArray, ".");
					foreach ($sFileArray as $sFile)
					{
						if (strstr($sFile, ".c") !== FALSE || strstr($sFile, ".cpp") !== FALSE)
							$sOutput .= " \"" . ProjectGen_GetRelativePath(realpath($sProjectDirectory), $pProject->GetBaseDirectory() . "/" . $sFile) . "\"";

					}
				$sOutput .= "\n";

				$sOutput .= "\tINCLUDE_DIRS ";



				$sIncludeDirectoryArray = $pProject->GetIncludeDirectoryArray(CONFIGURATION_DEBUG, ARCHITECTURE_32);
				for ($j = 0; $j < count($sIncludeDirectoryArray); $j++)
				{
					/**echo "***\n";
					echo $sBaseDirectory . "\n";
					echo $pProject->GetBaseDirectory() . "\n";
					echo $sIncludeDirectoryArray[$j] . "\n";

					echo realpath($sBaseDirectory) . "\n";
					echo realpath($pProject->GetBaseDirectory() . "/" . $sIncludeDirectoryArray[$j]) . "\n";**/


					$sIncludePath = $sIncludeDirectoryArray[$j];
					if ($sIncludePath[0] != "/")
					{
						$sIncludePath = realpath($pProject->GetBaseDirectory() . "/" . $sIncludeDirectoryArray[$j]);
						if ($sIncludePath === false)
							throw new Exception("Include path not found for " . $pProject->GetName() . ": " . $sIncludeDirectoryArray[$j]);
					}

					$sIncludePath = str_replace("\\", "/", $sIncludePath);

					$sOutput .= " \"" . $sIncludePath . "\"";

					//echo "inc: " . $sIncludePath . "\n";
					//echo realpath($sBaseDirectory . "/" . $pProject->GetName()) . "\n";
					//echo  " \"" . $sIncludePath . "\"\n\n\n";
				}

				if (count($sIncludeDirectoryArray) == 0)
					$sOutput .= " \"\"";

				$sOutput .= "\n";





				$sOutput .= " REQUIRES esp_event esp_timer esp_wifi nvs_flash fatfs sdmmc driver";

				$sDependancyArray = $pProject->GetDependancyArray();
				for ($j = 0; $j < count($sDependancyArray); $j++)
				{
					$sDependancy = $sDependancyArray[$j];
					$pDependancy = $pSolution->GetProjectByName($sDependancy);
					if ($pDependancy)
						$sOutput .= " " . $sDependancy;
				}

				$sOutput .= "\n";
				//$sOutput .= "\tREQUIRES spi_flash\n";
				$sOutput .= "\t)\n";


				if (count($sDefineArray) > 0)
					for ($j = 0; $j < count($sDefineArray); $j++)
						$sOutput .= "add_compile_definitions(" . $sDefineArray[$j] . ")\n";


				$sOutput .= "\ttarget_compile_options(\${COMPONENT_LIB} PRIVATE -Wno-maybe-uninitialized -Wno-misleading-indentation -Wno-error=unknown-pragmas -Wno-missing-field-initializers -Wno-unused-but-set-variable -Wno-implicit-fallthrough -Wno-delete-non-virtual-dtor)\n";
				file_put_contents($sProjectDirectory . "/CMakeLists.txt", $sOutput);
			}
		}
	}
	
?>
