<?php
	function ProjectGen_Dotnet10_Output($pSolution, $sAction)
	{
		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);

		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];

			if ($pProject->GetKind() != KIND_CONSOLE_APP)
				continue;

			$sCsprojDir = $sBaseDirectory . "/" . $pProject->GetName();
			if (!is_dir($sCsprojDir))
				mkdir($sCsprojDir);

			$sCsprojPath = $sCsprojDir . "/" . $pProject->GetName() . ".csproj";

			$sAssemblyName = $pProject->GetName();
			$sOutputType = "Exe";

			$sNuGetPackages = array();
			$sRecursiveDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);

			$sOutput =
				"<Project Sdk=\"Microsoft.NET.Sdk\">\n" .
				"  <PropertyGroup>\n" .
				"    <AssemblyName>" . $sAssemblyName . "</AssemblyName>\n" .
				"    <TargetFramework>net10.0</TargetFramework>\n" .
				"    <EnableDefaultCompileItems>false</EnableDefaultCompileItems>\n" .
				"    <Nullable>disable</Nullable>\n" .
				"    <ImplicitUsings>disable</ImplicitUsings>\n" .
				"    <OutputType>" . $sOutputType . "</OutputType>\n" .
				"  </PropertyGroup>\n\n" .
				"  <ItemGroup>\n";

				ProjectGen_Dotnet10_RecursiveAppendFiles($pProject->m_xFileArray, $sCsprojDir, $sOutput);

			$sOutput .= "  </ItemGroup>\n";

			$sAddedStaticLibProjects = array();
			if (count($sRecursiveDependancyArray) > 0)
			{
				$sOutput .= "  <ItemGroup>\n";
				for ($j = 0; $j < count($sRecursiveDependancyArray); $j++)
				{
					$sDependancy = $sRecursiveDependancyArray[$j];
					$pDependancyProject = $pSolution->GetProjectByName($sDependancy);
					if (!$pDependancyProject)
						continue;
					if ($pDependancyProject->GetKind() != KIND_STATIC_LIBRARY)
						continue;
					if (isset($sAddedStaticLibProjects[$pDependancyProject->GetName()]))
						continue;
					$sAddedStaticLibProjects[$pDependancyProject->GetName()] = true;

					ProjectGen_Dotnet10_RecursiveAppendFiles($pDependancyProject->m_xFileArray, $sCsprojDir, $sOutput);

					$sLibraryDependancyArray = $pDependancyProject->GetDependancyArray();
					for ($k = 0; $k < count($sLibraryDependancyArray); $k++)
					{
						$sLibraryDependency = $sLibraryDependancyArray[$k];
						if ($pSolution->GetProjectByName($sLibraryDependency))
							continue;

						if (preg_match('/^([A-Za-z0-9_.-]+)_(.+)$/', $sLibraryDependency, $m))
						{
							$sPackageName = $m[1];
							$sVersion = $m[2];
							$sKey = $sPackageName . "|" . $sVersion;
							if (!isset($sNuGetPackages[$sKey]))
								$sNuGetPackages[$sKey] = array("name" => $sPackageName, "version" => $sVersion);
							continue;
						}

						echo "ProjectGen_Dotnet10_Output warning: unknown dependency token '" . $sLibraryDependency . "' for project '" . $pDependancyProject->GetName() . "'\n";
					}
				}
				$sOutput .= "  </ItemGroup>\n";
			}

			$sDependancyArray = $pProject->GetDependancyArray();
			for ($j = 0; $j < count($sDependancyArray); $j++)
			{
				$sDependancy = $sDependancyArray[$j];

				if ($pSolution->GetProjectByName($sDependancy))
					continue;

				if (preg_match('/^([A-Za-z0-9_.-]+)_(.+)$/', $sDependancy, $m))
				{
					$sPackageName = $m[1];
					$sVersion = $m[2];
					$sKey = $sPackageName . "|" . $sVersion;
					if (!isset($sNuGetPackages[$sKey]))
						$sNuGetPackages[$sKey] = array("name" => $sPackageName, "version" => $sVersion);
					continue;
				}

				echo "ProjectGen_Dotnet10_Output warning: unknown dependency token '" . $sDependancy . "' for project '" . $pProject->GetName() . "'\n";
			}

			foreach ($sNuGetPackages as $sPackage)
			{
				$sOutput .=
					"  <ItemGroup>\n" .
					"    <PackageReference Include=\"" . $sPackage["name"] . "\" Version=\"" . $sPackage["version"] . "\" />\n" .
					"  </ItemGroup>\n\n";
			}

			$sOutput .=
				"</Project>\n";

			file_put_contents($sCsprojPath, $sOutput);
		}
	}

	function ProjectGen_Dotnet10_RecursiveAppendFiles($xFileArray, $sCsprojDir, &$sOutput)
	{
		foreach ($xFileArray as $xFile)
		{
			if ($xFile["sType"] == FILE_TYPE_DIRECTORY)
			{
				ProjectGen_Dotnet10_RecursiveAppendFiles($xFile["xFileArray"], $sCsprojDir, $sOutput);
				continue;
			}

			if ($xFile["sType"] != FILE_TYPE_FILE)
				continue;

			$sAbsPath = realpath($xFile["sPath"]);
			if ($sAbsPath === false)
				continue;

			$sRelPath = ProjectGen_GetRelativePath($sCsprojDir, $sAbsPath);
			$sRelPath = str_replace("\\", "/", $sRelPath);

			$sExtension = strtolower(pathinfo($sAbsPath, PATHINFO_EXTENSION));
			if ($sExtension == "cs")
				$sOutput .= "    <Compile Include=\"" . $sRelPath . "\" />\n";
			else
				$sOutput .= "    <None Include=\"" . $sRelPath . "\" />\n";
		}
	}

?>

