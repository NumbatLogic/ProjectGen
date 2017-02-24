<?php

	define("ACTION_WINDOWS_VS2008", "windows_vs2008");
	define("PLATFORM_WINDOWS_GMAKE", "windows_gmake"); // probably does not work
	define("ACTION_OSX_GMAKE", "osx_gmake");
	define("ACTION_EMSCRIPTEN_GMAKE", "emscripten_gmake");
	define("ACTION_IOS_XCODE", "ios_xcode");
	define("ACTION_ANDROID_CMAKE", "android_cmake");

	define("CONFIGURATION_DEBUG", "debug");
	define("CONFIGURATION_RELEASE", "release");
	
	define("ARCHITECTURE_32", "x32");
	define("ARCHITECTURE_64", "x64");

	define("KIND_CONSOLE_APP", "ConsoleApp");
	define("KIND_STATIC_LIBRARY", "StaticLib");
	define("KIND_WORKER", "Worker");

	define("FILE_TYPE_DIRECTORY", "directory");
	define("FILE_TYPE_FILE", "file");

	function ProjectGen_GetAction()
	{
		global $argv;
		if (isset($argv[1]))
		{
			switch (strtolower($argv[1]))
			{
				case ACTION_WINDOWS_VS2008: return ACTION_WINDOWS_VS2008;
				case PLATFORM_WINDOWS_GMAKE: return PLATFORM_WINDOWS_GMAKE;
				case ACTION_OSX_GMAKE: return ACTION_OSX_GMAKE;
				case ACTION_EMSCRIPTEN_GMAKE: return ACTION_EMSCRIPTEN_GMAKE;
				case ACTION_IOS_XCODE: return ACTION_IOS_XCODE;
				case ACTION_ANDROID_CMAKE: return ACTION_ANDROID_CMAKE;
			}
		}

		throw new Exception("No action!");
	}

	function ProjectGen_HashGuid($sString)
	{
		$sTemp = strtoupper(sha1($sString));
		return "{" .
			substr($sTemp, 0, 8)  . "-" .
			substr($sTemp, 8, 4) . "-" .
			substr($sTemp, 12, 4) . "-" .
			substr($sTemp, 16, 4) . "-" .
			substr($sTemp, 20, 12) .
			"}";
	}
	
	function ProjectGen_ParseDirectory($sDirectory, $sRegex)
	{
		$xFileArray = array();

			$pDirectory = opendir($sDirectory);
				while($sFile = readdir($pDirectory))
				{
					if ($sFile != "." && $sFile != ".." && $sFile != ".svn")
					{
						if (is_dir($sDirectory . "/" . $sFile))
						{
							$xFileArray[] = array("sType" => FILE_TYPE_DIRECTORY, "sName" => $sFile, "xFileArray" => ProjectGen_ParseDirectory($sDirectory . "/" . $sFile, $sRegex));
							continue;
						}
						

						//echo $sRegex . "    -    " .  $sDirectory . "/" . $sFile . "\n";
						//var_dump(preg_match($sRegex, $sDirectory . "/" . $sFile));
						if (preg_match($sRegex, $sDirectory . "/" . $sFile) != 0) // )//
						{
							$sExtension = strtolower(pathinfo($sDirectory . "/" . $sFile, PATHINFO_EXTENSION));
							$xFileArray[] = array("sType" => FILE_TYPE_FILE, "sName" => $sFile, "sPath" => realpath($sDirectory . "/" . $sFile), "sExtension" => $sExtension);
						}
					}
				}
			closedir($pDirectory);

		return $xFileArray;
	}

	abstract class Solution_Config
	{
		protected $m_sAction;
		
		public $m_pProjectArray;

		public function __construct($sAction)
		{
			$this->m_sAction = $sAction;
			$this->m_pProjectArray = array();
		}

		abstract public function GetName();

		public function GetProjectByName($sName)
		{
			for ($i = 0; $i < count($this->m_pProjectArray); $i++)
			{
				$pProject = $this->m_pProjectArray[$i];
				if ($pProject->GetName() == $sName)
					return $pProject;
			}
			return NULL;
		}
	}

	abstract class Project_Config
	{
		protected $m_sAction;
		public $m_xFileArray;
		public $m_xAssetArray;
		public function __construct($sAction)
		{
			$this->m_sAction = $sAction;
			$this->m_xFileArray = array();
			$this->m_xAssetArray = array();
		}

		abstract public function GetName();
		abstract public function GetKind();
		abstract public function GetBaseDirectory();


		// ios && android
		public function GetBundleIdentifier() { throw new Exception("Bundle identifier not set for " . $this->GetName()); }

		public function GetBuildOptionArray($sConfiguration, $sArchitecture) { return array(); }

		public function GetLinkFlags($sConfiguration, $sArchitecture) { return ""; }

		public function GetDependancyArray() { return array(); }
		public function GetIncludeDirectoryArray($sConfiguration, $sArchitecture) { return array(); }
		public function GetPostBuildCommandArray($sConfiguration, $sArchitecture) { return array(); }
 
		public function GetGuidA() { return ProjectGen_HashGuid($this->GetName()); }
		public function GetGuidB() { return ProjectGen_HashGuid($this->GetName() . ":)"); }
	}
	
	abstract class ProjectGen_OutFile
	{
		public function __construct()
		{
		}
	}

	$g_sConfigurationArray = array(
		//"Debug", "Release"
		CONFIGURATION_DEBUG,
		CONFIGURATION_RELEASE,
	);

	$g_sArchitectureArray = array(
		//"Win32", "x64"
		ARCHITECTURE_32,
		ARCHITECTURE_64,
	);

	include_once dirname(__FILE__) . "/ProjectGen_Xcode.php";
	include_once dirname(__FILE__) . "/ProjectGen_Gmake.php";
	include_once dirname(__FILE__) . "/ProjectGen_Cmake.php";
	
	function ProjectGen($pSolution)
	{
		global $g_sConfigurationArray;
		global $g_sArchitectureArray;

		$sAction = ProjectGen_GetAction();

		
		
		if (!is_dir("ProjectGen"))
			mkdir("ProjectGen");

		if ($sAction == ACTION_WINDOWS_VS2008)
		{
			// sln
			$sOutput = chr(0xEF) . chr(0xBB) . chr(0xBF) . "\r\n"; // BOM
			$sOutput .= "Microsoft Visual Studio Solution File, Format Version 10.00\r\n";
			$sOutput .= "# Visual Studio 2008\r\n";

			for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
			{
				$pProject = $pSolution->m_pProjectArray[$i];

				$sOutput .= "Project(\"" . $pProject->GetGuidA() . "\") = \"" . $pProject->GetName() . "\", \"" . $pProject->GetName() . "\\" . $pProject->GetName() . ".vcproj\", \"" . $pProject->GetGuidB() . "\"\r\n"; // "..\3rdParty\vs2008\3rdParty.vcproj"

					$sDependancyArray = $pProject->GetDependancyArray();
					if (count($sDependancyArray) > 0)
					{
						$sOutput .= "\tProjectSection(ProjectDependencies) = postProject\r\n";
						for ($j = 0; $j < count($sDependancyArray); $j++)
						{
							$sDependancy = $sDependancyArray[$j];
							$pDependancy = $pSolution->GetProjectByName($sDependancy);
							//throw new Exception("Project dependancy not found: " . $sDependancy);
							if ($pDependancy)
								$sOutput .= "\t\t" . $pDependancy->GetGuidB() . " = " . $pDependancy->GetGuidB() . "\r\n";
						}
						$sOutput .= "\tEndProjectSection\r\n";
					}
				$sOutput .= "EndProject\r\n";	
			}

			$sOutput .= "Global\r\n";

				$sOutput .= "\tGlobalSection(SolutionConfigurationPlatforms) = preSolution\r\n";
					foreach ($g_sConfigurationArray as $sConfiguration)
						foreach ($g_sArchitectureArray as $sArchitecture)
							$sOutput .= "\t\t" . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . " = " . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\r\n";
				$sOutput .= "\tEndGlobalSection\r\n";

				if (count($pSolution->m_pProjectArray) > 0)
				{
					$sOutput .= "\tGlobalSection(ProjectConfigurationPlatforms) = postSolution\r\n";
					for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
					{
						$pProject = $pSolution->m_pProjectArray[$i];
						

						foreach ($g_sConfigurationArray as $sConfiguration)
						{
							foreach ($g_sArchitectureArray as $sArchitecture)
							{
								$sOutput .= "\t\t" . $pProject->GetGuidB() . "." . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . ".ActiveCfg = " . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\r\n";
								$sOutput .= "\t\t" . $pProject->GetGuidB() . "." . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . ".Build.0 = " . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\r\n";
							}
						}
					}
					$sOutput .= "\tEndGlobalSection\r\n";
				}

				$sOutput .= "\tGlobalSection(SolutionProperties) = preSolution\r\n";
				$sOutput .= "\t\tHideSolutionNode = FALSE\r\n";
				$sOutput .= "\tEndGlobalSection\r\n";

			$sOutput .= "EndGlobal\r\n";

			$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
			if (!is_dir($sBaseDirectory))
				mkdir($sBaseDirectory);
			file_put_contents($sBaseDirectory . "/" . $pSolution->GetName() . ".sln", $sOutput);


			// vcproj
			for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
			{
				$pProject = $pSolution->m_pProjectArray[$i];

				$sOutput = "";

				$sOutput .= "<?xml version=\"1.0\" encoding=\"Windows-1252\"?>\n";
				$sOutput .= "<VisualStudioProject ProjectType=\"Visual C++\" Version=\"9.00\" Name=\"" . $pProject->GetName() . "\" ProjectGUID=\"" . $pProject->GetGuidB() . "\" RootNamespace=\"" . $pProject->GetName() . "\" Keyword=\"Win32Proj\">\n";

				$sOutput .= "\t<Platforms>\n";
					foreach ($g_sArchitectureArray as $sArchitecture)
						$sOutput .= "\t\t<Platform Name=\"" . ProjectGen_vs2008_Translate($sArchitecture) . "\"/>\n";
				$sOutput .= "\t</Platforms>\n";

				$sOutput .= "\t<ToolFiles>\n";
				$sOutput .= "\t</ToolFiles>\n";

				$sOutput .= "\t<Configurations>\n";

					foreach ($g_sConfigurationArray as $sConfiguration)
					{
						foreach ($g_sArchitectureArray as $sArchitecture)
						{
							
							$sIncludeDirectoryArray = $pProject->GetIncludeDirectoryArray($sConfiguration, $sArchitecture);
							for ($j = 0; $j < count($sIncludeDirectoryArray); $j++)
								$sIncludeDirectoryArray[$j] = ProjectGen_GetRelativePath(realpath($sBaseDirectory . "/" . $pProject->GetName()), realpath($pProject->GetBaseDirectory() . "/" . $sIncludeDirectoryArray[$j]));
			

							$sOutput .= "\t\t<Configuration Name=\"" . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\"" .
								" OutputDirectory=\"..\\..\\..\\..\\Bin\"" . 
								" IntermediateDirectory=\"obj\\" . $sArchitecture . "\\" . $sConfiguration . "\"" .
								" ConfigurationType=\"" . ($pProject->GetKind() == KIND_CONSOLE_APP ? "1" : "4") . "\"" .
								" CharacterSet=\"2\"" .
							">\n";

								$sOutput .= "\t\t\t<Tool Name=\"VCPreBuildEventTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCCustomBuildTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCXMLDataGeneratorTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCWebServiceProxyGeneratorTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCMIDLTool\"/>\n";

								$sOutput .= "\t\t\t<Tool" .
									" Name=\"VCCLCompilerTool\"" .
									" Optimization=\"" . ($sConfiguration == CONFIGURATION_DEBUG ? "0" : "3") . "\"" .
									" AdditionalIncludeDirectories=\"" . implode(";", $sIncludeDirectoryArray) . "\"" .
									" PreprocessorDefinitions=\"_WIN32;_CRT_SECURE_NO_WARNINGS" . ($sConfiguration == CONFIGURATION_DEBUG ? ";NB_DEBUG" : ";NDEBUG") . "\"" .
									($sConfiguration == CONFIGURATION_RELEASE ? " StringPooling=\"true\"" : "") .

									($sConfiguration == CONFIGURATION_DEBUG ? " BasicRuntimeChecks=\"3\"" : "") .
									
									" RuntimeLibrary=\"" . ($sConfiguration == CONFIGURATION_DEBUG ? "3" : "2") . "\"" .
									" EnableFunctionLevelLinking=\"true\"" .
									" UsePrecompiledHeader=\"0\"" .
									" WarningLevel=\"3\"" .
									" WarnAsError=\"true\"" .
									" ProgramDataBaseFileName=\"$(OutDir)\\" . $pProject->GetName() . ".pdb\"" .
									" DebugInformationFormat=\"" . ($sConfiguration == CONFIGURATION_DEBUG ? ($sArchitecture == ARCHITECTURE_32 ? "4" : "3") : "0") . "\"" .
								"/>\n";



								$sOutput .= "\t\t\t<Tool Name=\"VCManagedResourceCompilerTool\"/>\n";

								$sOutput .= "\t\t\t<Tool" .
									" Name=\"VCResourceCompilerTool\"" .
									" AdditionalIncludeDirectories=\"" . implode(";", $sIncludeDirectoryArray) . "\"" .
									" PreprocessorDefinitions=\"_WIN32;_CRT_SECURE_NO_WARNINGS\"" .
								"/>\n";

								$sOutput .= "\t\t\t<Tool Name=\"VCPreLinkEventTool\"/>\n";




								if ($pProject->GetKind() == KIND_STATIC_LIBRARY || $pProject->GetKind() == KIND_WORKER)
								{
									$sOutput .= "\t\t\t<Tool" .
										" Name=\"VCLibrarianTool\"" .
										" OutputFile=\"$(OutDir)\\" . $pProject->GetName() . ".lib\"" .
										" AdditionalOptions=\"/MACHINE:" . ($sArchitecture == ARCHITECTURE_32 ? "X86" : "X64") . "\"" .
									"/>\n";
									$sOutput .= "\t\t\t<Tool Name=\"VCALinkTool\"/>\n";
								}
								else
								{
									$AdditionalDependencies = "";
									//$sDependancyArray = $pProject->GetDependancyArray();
									$sDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);
									for ($j = 0; $j < count($sDependancyArray); $j++)
									{
										$sDependancy = $sDependancyArray[$j];
										$pDependancy = $pSolution->GetProjectByName($sDependancy);
										if ($pDependancy)
											$AdditionalDependencies .= "..\\..\\..\\..\\Bin\\" . $sDependancy . ".lib ";
										else
											$AdditionalDependencies .= $sDependancy . " ";
									}

									$sOutput .= "\t\t\t<Tool" .
										" Name=\"VCLinkerTool\"" .
										" AdditionalDependencies=\"" . $AdditionalDependencies . "\"" . // ..\..\..\Bin\\3rdParty.lib ..\..\..\Bin\Core.lib ..\..\..\Bin\Engine.lib ..\..\..\Library\Windows\GLEW\lib\glew32.lib ..\..\..\Library\Windows\GLFW\lib-msvc90\GLFW.lib opengl32.lib glu32.lib Ws2_32.lib ..\..\..\Library\Windows\libcurl\lib\dll-release\libcurl.lib
										" OutputFile=\"$(OutDir)\\" . $pProject->GetName() . ".exe\"" .
										" LinkIncremental=\"" . ($sConfiguration == CONFIGURATION_DEBUG ? "2" : "1") . "\"" .
										" AdditionalLibraryDirectories=\"\"" .
										" GenerateDebugInformation=\"" . ($sConfiguration == CONFIGURATION_DEBUG ? "true" : "false") . "\"" .
										($sConfiguration == CONFIGURATION_DEBUG ? " ProgramDataBaseFileName=\"$(OutDir)\\" . $pProject->GetName() . ".pdb\"" : "") .
										" SubSystem=\"1\"" .

										($sConfiguration == CONFIGURATION_RELEASE ? " OptimizeReferences=\"2\"" : "") .
										($sConfiguration == CONFIGURATION_RELEASE ? " EnableCOMDATFolding=\"2\"" : "") .
										
										" EntryPointSymbol=\"mainCRTStartup\"" .
										" TargetMachine=\"1\"" .
									"/>\n";
								}
								
								$sOutput .= "\t\t\t<Tool Name=\"VCManifestTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCXDCMakeTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCBscMakeTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCFxCopTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCAppVerifierTool\"/>\n";
								$sOutput .= "\t\t\t<Tool Name=\"VCWebDeploymentTool\"/>\n";

								$sOutput .= "\t\t\t<Tool Name=\"VCPostBuildEventTool\" CommandLine=\"" . implode("&#x0D;&#x0A;", $pProject->GetPostBuildCommandArray($sConfiguration, $sArchitecture)) . "\"/>\n";


							$sOutput .= "\t\t</Configuration>\n";
						}
					}

				$sOutput .= "\t</Configurations>\n";

				$sOutput .= "\t<References>\n";
				$sOutput .= "\t</References>\n";


				$sOutput .= "\t<Files>\n";
					$sOutput .= ProjectGen_vcproj_OutputDirectory($pProject->m_xFileArray, "\t\t");
				$sOutput .= "\t</Files>\n";


				$sOutput .= "</VisualStudioProject>\n";
				if (!is_dir($sBaseDirectory . "/" . $pProject->GetName()))
					mkdir($sBaseDirectory . "/" . $pProject->GetName());
				file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . "/" . $pProject->GetName() . ".vcproj", $sOutput);
				
				
				
				// .user
				$sOutput = "";
				$sOutput .= "<?xml version=\"1.0\" encoding=\"Windows-1252\"?>\n";
				$sOutput .= "<VisualStudioUserFile ProjectType=\"Visual C++\" Version=\"9.00\" ShowAllFiles=\"false\">\n";
					$sOutput .= "\t<Configurations>\n";
						foreach ($g_sConfigurationArray as $sConfiguration)
						{
							foreach ($g_sArchitectureArray as $sArchitecture)
							{
								$sOutput .= "\t\t<Configuration Name=\"" . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\">\n";
									$sOutput .= "\t\t\t<DebugSettings" .
										" Command=\"$(TargetPath)\"" .
										" WorkingDirectory=\"\$(OutDir)\"" .
										" CommandArguments=\"\"" .
										" Attach=\"false\"" .
										" DebuggerType=\"3\"" .
										" Remote=\"1\"" .
										" RemoteMachine=\"" . strtoupper(getenv("computername")) . "\"" .
										" RemoteCommand=\"\"" .
										" HttpUrl=\"\"" .
										" PDBPath=\"\"" .
										" SQLDebugging=\"\"" .
										" Environment=\"\"" .
										" EnvironmentMerge=\"true\"" .
										" DebuggerFlavor=\"\"" .
										" MPIRunCommand=\"\"" .
										" MPIRunArguments=\"\"" .
										" MPIRunWorkingDirectory=\"\"" .
										" ApplicationCommand=\"\"" .
										" ApplicationArguments=\"\"" .
										" ShimCommand=\"\"" .
										" MPIAcceptMode=\"\"" .
										" MPIAcceptFilter=\"\"" .
									"/>\n";
								$sOutput .= "\t\t</Configuration>\n";
							}
						}
					$sOutput .= "\t</Configurations>\n";
				$sOutput .= "</VisualStudioUserFile>";
				
				file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . "/" . $pProject->GetName() . ".vcproj." . getenv("computername") . "." . getenv("username") . ".user", $sOutput);
			}
		}
		else if ($sAction == ACTION_OSX_GMAKE || $sAction == ACTION_EMSCRIPTEN_GMAKE)
		{
			ProjetGen_Gmake_Output($pSolution, $sAction);
		}
		else if ($sAction == ACTION_IOS_XCODE)
		{
			ProjetGen_Xcode_Output($pSolution, $sAction);
		}
		else if ($sAction == ACTION_ANDROID_CMAKE)
		{
			ProjectGen_Cmake_Output($pSolution, $sAction);
		}
	}


	function ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject)
	{
		$sDependancyArray = $pProject->GetDependancyArray();
		for ($j = 0; $j < count($sDependancyArray); $j++)
		{
			$sDependancy = $sDependancyArray[$j];
			$pDependancy = $pSolution->GetProjectByName($sDependancy);
			if ($pDependancy)
			{
				$sSubDependancyArray = $pDependancy->GetDependancyArray();
				for ($k = 0; $k < count($sSubDependancyArray); $k++)
					if (!in_array($sSubDependancyArray[$k], $sDependancyArray))
						$sDependancyArray[] = $sSubDependancyArray[$k];
			}
			/*else
			{
				$sDependancyArray[] = $sSubDependancyArray[$k];
			}*/
		}
		return $sDependancyArray;
	}

	function ProjectGen_FlattenFileArray($xFileArray, $sPath)
	{
		$sFileArray = array();
		foreach ($xFileArray as $xFile)
		{
			if ($xFile["sType"] == FILE_TYPE_DIRECTORY)
			{
				$sFileArray = array_merge(
					$sFileArray,
					ProjectGen_FlattenFileArray($xFile["xFileArray"], ($sPath == "" ? "" : ($sPath . "/")) . $xFile["sName"]));
			}
			else
			{
				$sFileArray[] = $xFile["sName"];
			}
		}

		return $sFileArray;
	}


	function ProjectGen_GetBaseDirectory($sAction)
	{
		return "ProjectGen/" . $sAction;
	}

	
	
	/*function ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject)
	{
		$pDependancyArray = array();
		$sDependancyArray = $pProject->GetDependancyArray();
		
		while (count($sDependancyArray) > 0)
		{
			$sDependancy = $sDependancyArray[0];
			$pDependancy = $pSolution->GetProjectByName($sDependancy);
			
			for ($i = 0; $i < count($pDependancyArray); $i++)
			{
				if ($pDependancy == $pDependancyArray[$i])
					goto EndLoop;
					break;
			}
			
			
			$pDependancyArray[] = $pDependancy;
			$sDependancyArray = array_merge($sDependancyArray, $pDependancy->GetDependancyArray());
			
			EndLoop:
			$sDependancyArray = array_splice($sDependancyArray, 0, 1);
		}
		
		return $pDependancyArray;
	}*/
	
	//http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
	function ProjectGen_GetRelativePath($from, $to)
	{
		if (!is_string($to))
			throw new Exception("Not a string!");
			
	//	var_dump($to);

		//echo $from . " => " . $to;
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			// find first non-matching dir
			if($dir === $to[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from
				$remaining = count($from) - $depth;
				if($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}

		//echo " ~~~ " . implode('/', $relPath) . "\n";
		return implode('/', $relPath);
	}




	function ProjectGen_vs2008_Translate($sIn)
	{
		if ($sIn == CONFIGURATION_DEBUG)
			return "Debug";
		if ($sIn == CONFIGURATION_RELEASE)
			return "Release";
		if ($sIn == ARCHITECTURE_32)
			return "Win32";
		if ($sIn == ARCHITECTURE_64)
			return "x64";
		return $sIn;
	}

	function ProjectGen_vcproj_OutputDirectory($xFileArray, $sTab)
	{
		global $g_sConfigurationArray;
		global $g_sArchitectureArray;

		$sOuptut = "";

		foreach ($xFileArray as $xFile)
		{
			if ($xFile["sType"] == FILE_TYPE_DIRECTORY)
			{
				$sOuptut .= $sTab . "<Filter Name=\"" . $xFile["sName"] . "\" Filter=\"\">\n";
				$sOuptut .= ProjectGen_vcproj_OutputDirectory($xFile["xFileArray"], $sTab . "\t");
				$sOuptut .= $sTab . "</Filter>\n"; 
			}
			else
			{
				$sOuptut .= $sTab . "<File RelativePath=\"" . $xFile["sPath"] . "\">\n";
				if ($xFile["sExtension"] == "c")
				{
					foreach ($g_sConfigurationArray as $sConfiguration)
					{
						foreach ($g_sArchitectureArray as $sArchitecture)
						{
							$sOuptut .= $sTab . "\t<FileConfiguration Name=\"" . ProjectGen_vs2008_Translate($sConfiguration) . "|" . ProjectGen_vs2008_Translate($sArchitecture) . "\">\n";
							$sOuptut .= $sTab . "\t\t<Tool Name=\"VCCLCompilerTool\" CompileAs=\"1\"/>\n";
							$sOuptut .= $sTab . "\t</FileConfiguration>\n";
						}
					}
				}
				$sOuptut .= $sTab . "</File>\n";

			}
		}
		return $sOuptut;
	}

	
	
?>
