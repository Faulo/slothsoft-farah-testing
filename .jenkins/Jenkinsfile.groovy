def runTests(def versions) {
	for (version in versions) {
		def image = "faulo/farah:${version}"

		stage("PHP: ${version}") {
			dir('.reports') {
				deleteDir()
			}

			def dockerTool = tool(type: 'dockerTool', name: 'Default') + "/bin/docker"

			callShell "${dockerTool} pull ${image}"

			withDockerContainer(image: image, toolName: 'Default') {
				catchError(stageResult: 'UNSTABLE', buildResult: 'UNSTABLE', catchInterruptions: false) {
					if (isUnix()) {
						sh 'install -d -m 0755 /etc/apt/keyrings'
						sh 'apt update && apt install wget -y'
						sh 'wget -q https://packages.mozilla.org/apt/repo-signing-key.gpg -O- | tee /etc/apt/keyrings/packages.mozilla.org.asc > /dev/null'
						sh 'echo "deb [signed-by=/etc/apt/keyrings/packages.mozilla.org.asc] https://packages.mozilla.org/apt mozilla main" | tee -a /etc/apt/sources.list.d/mozilla.list > /dev/null'
						sh 'apt update && apt install firefox -y'
					}

					callShell 'composer update --prefer-lowest'
					callShell "composer exec phpunit -- --log-junit .reports/${version}.xml"
				}
			}

			dir('.reports') {
				junit "*"
			}
		}
	}
}

pipeline {
	agent none
	options {
		disableConcurrentBuilds()
		disableResume()
	}
	environment {
		COMPOSER_PROCESS_TIMEOUT = '3600'
	}
	stages {
		stage('Linux') {
			agent {
				label 'docker && linux'
			}
			steps {
				script {
					runTests(["7.4", "8.0", "8.1", "8.2", "8.3"])
				}
			}
		}
		stage('Windows') {
			agent {
				label 'docker && windows'
			}
			steps {
				script {
					runTests(["7.4", "8.0", "8.1", "8.2", "8.3"])
				}
			}
		}
	}
}