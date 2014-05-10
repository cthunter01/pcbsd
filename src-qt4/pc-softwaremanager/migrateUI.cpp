#include "migrateUI.h"
#include "ui_migrateUI.h"
#include <QMessageBox>

MigrateUI::MigrateUI() : QMainWindow(), ui(new Ui::MigrateUI){
  ui->setupUi(this); //load the designer file	
  ui->group_reboot->setVisible(false);
  proc = new DLProcess(this);
	proc->setParentWidget(this);
	proc->setDLType("PKG");
	proc->setProcessChannelMode(QProcess::MergedChannels);
  connect(proc, SIGNAL(finished(int, QProcess::ExitStatus)), this, SLOT(procFinished()) );
  connect(proc, SIGNAL(UpdateMessage(QString)), this, SLOT(updateProgress(QString)) );
  connect(proc, SIGNAL(UpdatePercent(QString,QString,QString)), this, SLOT(updatePercent(QString,QString,QString)) );
  connect(ui->push_start, SIGNAL(clicked()), this, SLOT(startMigration()) );
  connect(ui->push_reboot, SIGNAL(clicked()), this, SLOT(restartSystem()) );
  connect(ui->actionClose, SIGNAL(triggered()), this, SLOT(closeWindow()) );
}
	
void MigrateUI::slotSingleInstance(){
  this->raise();
  this->showNormal();
  this->activateWindow();
}

void MigrateUI::closeWindow(){
  this->close();	
}
	
void MigrateUI::startMigration(){
  ui->group_migrate->setVisible(false);
  proc->start("pbi migrate");
}
	
void MigrateUI::procFinished(){
  ui->group_reboot->setVisible(true);
}
	
void MigrateUI::updateProgress(QString msg){
  ui->text_progress->append(msg);

  QString line = msg;

  if ( line.indexOf("PKGCONFLICTS: ") == 0 ) {
     QString tmp = line;
     tmp.replace("PKGCONFLICTS: ", "");
     ConflictList = tmp;
  }
  else if ( line.indexOf("PKGREPLY: ") == 0 ) {
     QString ans;
     QString tmp = line;
     tmp.replace("PKGREPLY: ", "");
     QMessageBox msgBox;
     msgBox.setText(tr("The following packages are causing conflicts with the selected changes and can be automatically removed. Continue?") + "\n" + ConflictList);
     msgBox.setStandardButtons(QMessageBox::Yes|QMessageBox::No);
     msgBox.setDetailedText(getConflictDetailText());
     msgBox.setDefaultButton(QMessageBox::No);
     if ( msgBox.exec() == QMessageBox::Yes) {
        // We will try to fix conflicts
        ans="yes";
     } else {
       // We will fail :(
       QMessageBox::warning(this, tr("Package Conflicts"),
       tr("You may need to manually fix the conflicts before trying again."),
       QMessageBox::Ok,
       QMessageBox::Ok);
       ans="no";
     }

     QFile pkgTrig( tmp );
     if ( pkgTrig.open( QIODevice::WriteOnly ) ) {
        QTextStream streamTrig( &pkgTrig );
        streamTrig << ans;
        pkgTrig.close();
        ConflictList.clear(); //already sent an answer - clear the internal list
     }
  }
}

void MigrateUI::updatePercent(QString percent, QString size, QString filename){
  QString msg = QString(tr("Downloading: %1 (%2 of %3)")).arg(filename, percent, size);
  ui->text_progress->append(msg);
}
void MigrateUI::restartSystem(){
  QProcess::startDetached("shutdown -r now");
  this->close();
}


QString MigrateUI::getConflictDetailText() {

  QStringList ConList = ConflictList.split(" ");
  QStringList tmpDeps;
  QString retText;

  for (int i = 0; i < ConList.size(); ++i) {
    QProcess p;
    tmpDeps.clear();

    p.start("pkg", QStringList() << "rquery" << "%rn-%rv" << ConList.at(i));

    if(p.waitForFinished()) {
      while (p.canReadLine()) {
        tmpDeps << p.readLine().simplified();
      }
    }
    retText+= ConList.at(i) + " " + tr("required by:") + "\n" + tmpDeps.join(" ");
  }

  return retText;
}
