<?php
	function ProjectGen_Dotnet10_Output($pSolution, $sAction)
	{
		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);

		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];

			if ($pProject->GetKind() != KIND_STATIC_LIBRARY && $pProject->GetKind() != KIND_CONSOLE_APP)
				continue;

			$sCsprojDir = $sBaseDirectory . "/" . $pProject->GetName();
			if (!is_dir($sCsprojDir))
				mkdir($sCsprojDir);

			$sCsprojPath = $sCsprojDir . "/" . $pProject->GetName() . ".csproj";

			$sAssemblyName = $pProject->GetName();
			$sOutputType = ($pProject->GetKind() == KIND_CONSOLE_APP ? "Exe" : "Library");

			$sProjectReferenceBlock = "";
			$sNuGetPackageBlock = "";
			$sDependancyArray = $pProject->GetDependancyArray();
			if (count($sDependancyArray) > 0)
			{
				$sProjectReferenceBlock .= "  <ItemGroup>\n";
				for ($j = 0; $j < count($sDependancyArray); $j++)
				{
					$sDependancy = $sDependancyArray[$j];

					$pDependancyProject = $pSolution->GetProjectByName($sDependancy);
					if ($pDependancyProject)
					{
						$sRefCsprojDir = $sBaseDirectory . "/" . $pDependancyProject->GetName();
						$sRefCsprojPath = $sRefCsprojDir . "/" . $pDependancyProject->GetName() . ".csproj";
						$sRelRefPath = ProjectGen_GetRelativePath($sCsprojDir, $sRefCsprojPath);
						$sRelRefPath = str_replace("\\", "/", $sRelRefPath);

						$sProjectReferenceBlock .= "    <ProjectReference Include=\"" . $sRelRefPath . "\" />\n";
						continue;
					}

					if (preg_match('/^([A-Za-z0-9_.-]+)_(.+)$/', $sDependancy, $m))
					{
						$sPackageName = $m[1];
						$sVersion = $m[2];

						$sNuGetPackageBlock .=
							"  <ItemGroup>\n" .
							"    <PackageReference Include=\"" . $sPackageName . "\" Version=\"" . $sVersion . "\" />\n" .
							"  </ItemGroup>\n\n";
						continue;
					}
					
					echo "ProjectGen_Dotnet10_Output warning: unknown dependency token '" . $sDependancy . "' for project '" . $pProject->GetName() . "'\n";
					continue;

				}
				$sProjectReferenceBlock .= "  </ItemGroup>\n\n";
			}

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

			$sOutput .= "\n" . $sProjectReferenceBlock;
			
			$sOutput .= $sNuGetPackageBlock;

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

