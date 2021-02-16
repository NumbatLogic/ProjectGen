<?php

	function ProjectGen_Esp_Idf_Output($pSolution, $sAction)
	{
		global $g_sConfigurationArray;
		global $g_sArchitectureArray;

		$pMainProject = null;

		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);

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
						if (strstr($sFile, ".c") !== FALSE)
							$sOutput .= " \"" . ProjectGen_GetRelativePath(realpath($sProjectDirectory), $pProject->GetBaseDirectory() . "/" . $sFile) . "\"";

					}
				$sOutput .= "\n";

				$sOutput .= "\tINCLUDE_DIRS \"\"\n";
				//$sOutput .= "\tREQUIRES spi_flash\n";
				$sOutput .= "\t)\n";
				$sOutput .= "\ttarget_compile_options(\${COMPONENT_LIB} PRIVATE -Wno-maybe-uninitialized -Wno-misleading-indentation -Wno-error=unknown-pragmas -Wno-missing-field-initializers -Wno-unused-but-set-variable -Wno-implicit-fallthrough -Wno-delete-non-virtual-dtor)\n";
				file_put_contents($sProjectDirectory . "/CMakeLists.txt", $sOutput);





				/*$sOutput = "";
				$sOutput .= "CFLAGS += -Wno-error=maybe-uninitialized -Wno-error=misleading-indentation\n";
				$sOutput .= "CPPFLAGS += -Wno-error=maybe-uninitialized -Wno-error=misleading-indentation\n";
				file_put_contents($sProjectDirectory . "/component.mk", $sOutput);*/
			}
		}



		

		/*

		// Makefile
		$sOutput = "";

		$sOutput .= "ifndef config\n";
			$sOutput .= "  config=debug32\n";
		$sOutput .= "endif\n";
		$sOutput .= "export config\n\n";

		$sOutput .= "PROJECTS :=";
			for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
			{
				$pProject = $pSolution->m_pProjectArray[$i];
				$sOutput .= " " . $pProject->GetName();
			}
		$sOutput .= "\n\n";

		$sOutput .= ".PHONY: all clean help $(PROJECTS)\n\n";

		$sOutput .= "all: $(PROJECTS)\n\n";

		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			$sDependancyArray = $pProject->GetDependancyArray();

			$sOutput .= $pProject->GetName() . ":";
				for ($j = 0; $j < count($sDependancyArray); $j++)
				{
					$sDependancy = $sDependancyArray[$j];
					$pDependancy = $pSolution->GetProjectByName($sDependancy);
					if ($pDependancy)
						$sOutput .= " " . $sDependancy;
				}
			$sOutput .= "\n";

			$sOutput .= "\t@echo \"==== Building " . $pProject->GetName() . " ($(config)) ====\"\n";
			$sOutput .= "\t@\${MAKE} --no-print-directory -C ./" . $pProject->GetName() . " -f Makefile\n\n";
		}

		$sOutput .= "clean:\n";
		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			$sOutput .= "\t@\${MAKE} --no-print-directory -C ./" . $pProject->GetName() . " -f Makefile clean\n";
		}

		file_put_contents($sBaseDirectory . "/Makefile", $sOutput);






		// project Makefile
		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			$sOutput = "";
			$sOutput .= "ifndef config\n";
				$sOutput .= "  config=debug32\n";
			$sOutput .= "endif\n\n";

			$sOutput .= "ifndef verbose\n";
				$sOutput .= "  SILENT = @\n";
			$sOutput .= "endif\n\n";

			if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
			{
				$sOutput .= "CC = emcc\n";
			//	$sOutput .= "CXX = emcc\n";
			//	$sOutput .= "AR = emcc\n\n";
			}
			else
			{
				$sOutput .= "CC = gcc\n";
				$sOutput .= "CXX = g++\n";
			//	$sOutput .= "AR = ar\n\n";
			}



			foreach ($g_sConfigurationArray as $sConfiguration)
			{
				foreach ($g_sArchitectureArray as $sArchitecture)
				{
					$sOutput .= "ifeq ($(config)," . $sConfiguration . ($sArchitecture == ARCHITECTURE_32 ? "32" : "64") . ")\n";
						$sOutput .= "  OBJDIR = obj/" . $sArchitecture . "/" . $sConfiguration . "\n";
						$sOutput .= "  TARGETDIR = ../../../../Bin\n";

						$sTarget =  $pProject->GetName();
						if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
						{
							if ($pProject->GetKind() == KIND_STATIC_LIBRARY)
								$sTarget = "lib" . $sTarget . ".lo";
							elseif ($pProject->GetKind() == KIND_WORKER)
								$sTarget = $sTarget . ".js";
							else
								$sTarget = $sTarget . ".html";
						}
						elseif ($pProject->GetKind() == KIND_STATIC_LIBRARY || $pProject->GetKind() == KIND_WORKER)
							$sTarget = "lib" . $sTarget . ".a";
						elseif ($sAction == PLATFORM_WINDOWS_GMAKE)
							$sTarget = $sTarget . ".exe";

						$sOutput .= "  TARGET = $(TARGETDIR)/" . $sTarget . "\n";


						//if ($sConfiguration == CONFIGURATION_RELEASE)
						$sOutput .= "  DEFINES +=";
							if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
								$sOutput .= " -DEMSCRIPTEN";

							if ($sConfiguration == CONFIGURATION_DEBUG)
								$sOutput .= " -DNB_DEBUG";
							
						$sOutput .= "\n"; // -D_EMSCRIPTEN -D_CRT_SECURE_NO_WARNINGS\n";

						$sOutput .= "  INCLUDES +=";



						$sIncludeDirectoryArray = $pProject->GetIncludeDirectoryArray($sConfiguration, $sArchitecture);
						for ($j = 0; $j < count($sIncludeDirectoryArray); $j++)
						{
							$sIncludePath = $sIncludeDirectoryArray[$j];
							if ($sIncludePath[0] != "/")
							{
								$sIncludePath = realpath($pProject->GetBaseDirectory() . "/" . $sIncludeDirectoryArray[$j]);
								if ($sIncludePath === false)
									throw new Exception("Include path not found for " . $pProject->GetName() . ": " . $sIncludeDirectoryArray[$j]);
							}
						
							$sOutput .= " -I" . ProjectGen_GetRelativePath(realpath($sBaseDirectory . "/" . $pProject->GetName()), $sIncludePath);
						}
						$sOutput .= "\n";

						//$sOutput .= "  ALL_CPPFLAGS += $(CPPFLAGS) -MMD -MP $(DEFINES) $(INCLUDES)\n";
						if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
							$sOutput .= "  ALL_CFLAGS += $(CFLAGS) -MMD -MP $(DEFINES) $(INCLUDES) $(ARCH) -Werror " . ($sConfiguration == CONFIGURATION_RELEASE ? " -O3" : "-g") . " " . implode(" ", $pProject->GetBuildOptionArray($sConfiguration, $sArchitecture)) . " -Wno-dollar-in-identifier-extension\n";
						else
							$sOutput .= "  ALL_CFLAGS += $(CFLAGS) -MMD -MP $(DEFINES) $(INCLUDES) $(ARCH) -Werror " . ($sConfiguration == CONFIGURATION_RELEASE ? " -O3" : "-g") . " -m" . ($sArchitecture == ARCHITECTURE_32 ? "32" : "64") . " " . implode(" ", $pProject->GetBuildOptionArray($sConfiguration, $sArchitecture)) . "\n";
						//-std=c89 -Werror -Wall -Wextra -Wmissing-prototypes -Wstrict-prototypes -Wold-style-definition -pedantic -Wno-comment -Wno-newline-eof -Wno-long-long -Wno-overlength-strings -Wno-unused-parameter -Wno-empty-translation-unit\n";
						//else
						//	$sOutput .= "  ALL_CFLAGS += $(CFLAGS) $(ALL_CPPFLAGS) $(ARCH) -Werror -g -m" . ($sArchitecture == ARCHITECTURE_32 ? "32" : "64") . " " . implode(" ", $pProject->GetBuildOptionArray($sConfiguration, $sArchitecture)) . "\n";
						//-std=c89 -Werror -Wall -Wextra -Wmissing-prototypes -Wstrict-prototypes -Wold-style-definition -pedantic -Wno-comment -Wno-newline-eof -Wno-long-long -Wno-overlength-strings -Wno-unused-parameter -Wno-empty-translation-unit\n";

					//	$sOutput .= "  ALL_CXXFLAGS += $(CXXFLAGS) $(ALL_CFLAGS)\n";
					//	$sOutput .= "  ALL_RESFLAGS += $(RESFLAGS) $(DEFINES) $(INCLUDES)\n";
						
						if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
							$sOutput .= "  ALL_LDFLAGS += \$(LDFLAGS)" . ($sConfiguration == CONFIGURATION_RELEASE ? " -O3" : "")  . " " . $pProject->GetLinkFlags($sConfiguration, $sArchitecture) . "\n";
						else
							$sOutput .= "  ALL_LDFLAGS += \$(LDFLAGS)" . ($sConfiguration == CONFIGURATION_RELEASE ? " -s" : "")  . " -m" . ($sArchitecture == ARCHITECTURE_32 ? "32" : "64") . " " . $pProject->GetLinkFlags($sConfiguration, $sArchitecture) . "\n";


						//-L/usr/lib" . ($sArchitecture == ARCHITECTURE_32 ? "32" : "64") . " 
						
						
						$sDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);
						
						$sInternalLibrary = "";
						$sExternalLibrary = "";
						for ($j = 0; $j < count($sDependancyArray); $j++)
						{
							$sDependancy = $sDependancyArray[$j];
							$pDependancy = $pSolution->GetProjectByName($sDependancy);
							if ($pDependancy)
							{
								if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
								{
									if ($pDependancy->GetKind() == KIND_STATIC_LIBRARY) // workers get loaded differently
										$sInternalLibrary .= " ../../../../Bin/lib" . $sDependancy . ".lo";
								}
								else
								{
									$sInternalLibrary .= " ../../../../Bin/lib" . $sDependancy . ".a";
								}
							}
							else
								$sExternalLibrary .=  " " . $sDependancy;
						}

						$sOutput .= "  LDDEPS += " . $sInternalLibrary . $sExternalLibrary . "\n";
						$sOutput .= "  LIBS += $(LDDEPS)\n";


						if ($sAction == ACTION_EMSCRIPTEN_GMAKE)
						{
							if ($pProject->GetKind() == KIND_STATIC_LIBRARY)
								$sOutput .= "  LINKCMD = $(CC) \$(OBJECTS) -o \$(TARGET)\n";
							elseif ($pProject->GetKind() == KIND_WORKER)
								$sOutput .= "  LINKCMD = $(CC)  -o \$(TARGET) \$(OBJECTS) \$(RESOURCES) \$(ARCH) \$(ALL_LDFLAGS) \$(LIBS) -s TOTAL_MEMORY=268435456 -s EXPORTED_FUNCTIONS=\"['_nbJob_Test', '_nbSvgGroup_Prepare_Job', '_nbBfxrLibrary_Prepare_Job']\" -s BUILD_AS_WORKER=1\n"; // -s ASSERTIONS=2 -s SAFE_HEAP=1\n";
							else
								$sOutput .= "  LINKCMD = $(CC) -o \$(TARGET) \$(OBJECTS) \$(RESOURCES) \$(ARCH) \$(ALL_LDFLAGS) \$(LIBS) -s TOTAL_MEMORY=268435456 -s NO_EXIT_RUNTIME=1 -s AGGRESSIVE_VARIABLE_ELIMINATION=1\n"; // -s ASSERTIONS=2 -s SAFE_HEAP=1\n";
								//16777216 = 16mb
						}
						elseif ($pProject->GetKind() == KIND_STATIC_LIBRARY || $pProject->GetKind() == KIND_WORKER)
							$sOutput .= "  LINKCMD = ar -rcs \$(TARGET) \$(OBJECTS)\n";
						else
							$sOutput .= "  LINKCMD = $(CXX) -o \$(TARGET) \$(OBJECTS) \$(RESOURCES) \$(ARCH) \$(ALL_LDFLAGS) \$(LIBS)\n";
						
						$sOutput .= "  define PREBUILDCMDS\n";
						$sOutput .= "  endef\n";
						$sOutput .= "  define PRELINKCMDS\n";
						$sOutput .= "  endef\n";
						$sOutput .= "  define POSTBUILDCMDS\n";
						$sOutput .= "  endef\n";
					$sOutput .= "endif\n\n";
				}
			}

			

			$sFileArray = ProjectGen_FlattenFileArray($pProject->m_xFileArray, "");
			$sOutput .= "OBJECTS := \\\n";
			foreach ($sFileArray as $sFile)
			{
				if (strstr($sFile, ".c") !== FALSE)
					$sOutput .= "  \$(OBJDIR)/" . str_replace(array(".cpp", ".c"), array(".o", ".o"), $sFile) . " \\\n";

			}

			$sOutput .= "\nRESOURCES := \\\n\n";

			$sOutput .= "SHELLTYPE := msdos\n";
			$sOutput .= "ifeq (,$(ComSpec)$(COMSPEC))\n";
				$sOutput .= "  SHELLTYPE := posix\n";
			$sOutput .= "endif\n";
			$sOutput .= "ifeq (/bin,$(findstring /bin,$(SHELL)))\n";
				$sOutput .= "  SHELLTYPE := posix\n";
			$sOutput .= "endif\n\n";

			$sOutput .= ".PHONY: clean prebuild prelink\n\n";

			$sOutput .= "all: $(TARGETDIR) $(OBJDIR) prebuild prelink $(TARGET)\n";
				$sOutput .= "\t@:\n\n";

			$sOutput .= "$(TARGET): $(GCH) $(OBJECTS) $(LDDEPS) $(RESOURCES)\n";
				$sOutput .= "\t@echo Linking " . $pProject->GetName() . "\n";
				$sOutput .= "\t$(SILENT) $(LINKCMD)\n";
				$sOutput .= "\t$(POSTBUILDCMDS)\n\n";

			$sOutput .= "$(TARGETDIR):\n";
				$sOutput .= "\t@echo Creating $(TARGETDIR)\n";
			$sOutput .= "ifeq (posix,$(SHELLTYPE))\n";
				$sOutput .= "\t$(SILENT) mkdir -p $(TARGETDIR)\n";
			$sOutput .= "else\n";
				$sOutput .= "\t$(SILENT) mkdir $(subst /,\\,$(TARGETDIR))\n";
			$sOutput .= "endif\n\n";

			$sOutput .= "$(OBJDIR):\n";
				$sOutput .= "\t@echo Creating $(OBJDIR)\n";
			$sOutput .= "ifeq (posix,$(SHELLTYPE))\n";
				$sOutput .= "\t$(SILENT) mkdir -p $(OBJDIR)\n";
			$sOutput .= "else\n";
				$sOutput .= "\t$(SILENT) mkdir $(subst /,\\,$(OBJDIR))\n";
			$sOutput .= "endif\n\n";

			$sOutput .= "clean:\n";
				$sOutput .= "\t@echo Cleaning " . $pProject->GetName() . "\n";
			$sOutput .= "ifeq (posix,$(SHELLTYPE))\n";
				$sOutput .= "\t$(SILENT) rm -f  $(TARGET)\n";
				$sOutput .= "\t$(SILENT) rm -rf $(OBJDIR)\n";
			$sOutput .= "else\n";
				$sOutput .= "\t$(SILENT) if exist $(subst /,\\,$(TARGET)) del $(subst /,\\,$(TARGET))\n";
				$sOutput .= "\t$(SILENT) if exist $(subst /,\\,$(OBJDIR)) rmdir /s /q $(subst /,\\,$(OBJDIR))\n";
			$sOutput .= "endif\n\n";

			$sOutput .= "prebuild:\n";
				$sOutput .= "\t$(PREBUILDCMDS)\n\n";

			$sOutput .= "prelink:\n";
				$sOutput .= "\t$(PRELINKCMDS)\n\n";

			$sOutput .= "ifneq (,$(PCH))\n";
			$sOutput .= "$(GCH): $(PCH)\n";
				$sOutput .= "\t@echo $(notdir $<)\n";
				$sOutput .= "\t$(SILENT) $(CXX) -x c++-header $(ALL_CXXFLAGS) -MMD -MP $(DEFINES) $(INCLUDES) -o \"$@\" -MF \"$(@:%.gch=%.d)\" -c \"$<\"\n";
			$sOutput .= "endif\n\n";

			//echo realpath($sBaseDirectory . "/" . $pProject->GetName()) . " => " . $pProject->GetBaseDirectory() . "\n";
			//echo ProjectGen_GetRelativePath(realpath($sBaseDirectory . "/" . $pProject->GetName()), $pProject->GetBaseDirectory()) . "\n\n";

			$xFileArray = array($pProject->m_xFileArray);
			$sDirectoryArray = array(ProjectGen_GetRelativePath(realpath($sBaseDirectory . "/" . $pProject->GetName()), $pProject->GetBaseDirectory()));
			$nFileIndex = array(0);
			$sPathArray = array("");

			while (count($xFileArray) > 0)
			{
				$nIndex = count($xFileArray)-1;

				if ($nFileIndex[$nIndex] >= count($xFileArray[$nIndex]))
				{
					array_pop($xFileArray);
					array_pop($sDirectoryArray);
					array_pop($nFileIndex);
					array_pop($sPathArray);
					continue;
				}

				$xFile = $xFileArray[$nIndex][$nFileIndex[$nIndex]];
				$sDirectory = $sDirectoryArray[$nIndex];

				if ($xFile["sType"] == FILE_TYPE_DIRECTORY)
				{
					$xFileArray[] = $xFile["xFileArray"];
					$sDirectoryArray[] = $sDirectory . $xFile["sName"] . "/";
					$nFileIndex[] = 0;
					$sPathArray[] = $sPathArray[$nIndex] . $xFile["sName"] . "_";

					$nFileIndex[$nIndex]++;
					continue;
				}

				if ($xFile["sExtension"] == "c" || $xFile["sExtension"] == "cpp")
				{
					$sOutput .= "$(OBJDIR)/" . str_replace(array(".cpp", ".c"), array(".o", ".o"), $sPathArray[$nIndex] . $xFile["sName"]) . ": " . $sDirectory . $xFile["sName"] . "\n";
						$sOutput .= "\t@echo $(notdir $<)\n";
						$sOutput .= "\t$(SILENT) $(CC) $(ALL_CFLAGS) $(FORCE_INCLUDE) -o \"$@\" -MF $(@:%.o=%.d) -c \"$<\"\n\n";
				}

				$nFileIndex[$nIndex]++;
			}

			$sOutput .= "-include $(OBJECTS:%.o=%.d)\n";
			$sOutput .= "ifneq (,$(PCH))\n";
				$sOutput .= "  -include $(OBJDIR)/$(notdir $(PCH)).d\n";
			$sOutput .= "endif\n";

			file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . "/" . "Makefile", $sOutput);
		}*/
	}
	
?>
