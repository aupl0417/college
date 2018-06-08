using System;
using System.IO;
using System.Collections.Generic;
using System.Text;
using System.Windows.Forms;
using Newtonsoft.Json;
using System.Diagnostics;

namespace MTO
{
    public partial class Form1 : Form
    {
        //配置文件路径
        //private string ConfigFlie = System.Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\" + "cto.conf";
        private string ConfigFlie = Environment.CurrentDirectory + @"\config.json";

        //项目在master分支的路径
        private string AppMasterPath = "";

        //项目在online分支的路径
        private string AppOnlinePath = "";

        //git所在目录（有的电脑git没有加入PATH，所以还是直接使用cmd算了）
        private string CmdDir = @"C:\Windows\System32\cmd.exe";

        //配置数据
        private Dictionary<string, Dictionary<string, string>> Config;

        public Form1()
        {
            InitializeComponent();
        }

        //设置master路径
        private void linkLabel1_LinkClicked(object sender, LinkLabelLinkClickedEventArgs e)
        {
            //显示目录选择器
            folderBrowserDialog1.RootFolder = Environment.SpecialFolder.Desktop;
            if (folderBrowserDialog1.ShowDialog() == DialogResult.OK)
            {
                string dir = folderBrowserDialog1.SelectedPath.Replace('\\', '/') + '/';
                //显示到文本框
                textBox3.Text = dir;

                //保存到配置文件
                this.SetConfig("Base", "MasterPath", dir);
            }
        }

        //设置online路径
        private void linkLabel2_LinkClicked(object sender, LinkLabelLinkClickedEventArgs e)
        {
            //显示目录选择器
            folderBrowserDialog1.RootFolder = Environment.SpecialFolder.Desktop;
            if (folderBrowserDialog1.ShowDialog() == DialogResult.OK)
            {
                string dir = folderBrowserDialog1.SelectedPath.Replace('\\', '/') + '/';
                //显示到文本框
                textBox4.Text = dir;

                //保存到配置文件
                this.SetConfig("Base", "OnlinePath", dir);
            }
        }

        //程序启动时的动作
        private void Form1_Load(object sender, EventArgs e)
        {
            //程序启动时显示霸王条款
            DialogResult ok = MessageBox.Show("作者声明：本软件不保证功能的完整性、安全性和稳定性。\n使用本软件所产生的一切后果由使用者自己承担。\n您是否同意此霸王条款？", "免责声明", MessageBoxButtons.YesNo, MessageBoxIcon.Warning);
            if (ok == DialogResult.No) this.Close();

            //Debug.WriteLine(JsonConvert.SerializeObject(this.Config));

            //（有的电脑git没有加入PATH，所以还是直接使用cmd算了）
            //this.AddLog("获取git-cmd.exe所在目录......");
            //this.GitDir = GetGitDir();
            //this.AddLog("获取git-cmd.exe所在目录OK：" + this.GitDir);

            this.AddLog("读取配置文件......");
            //读取（创建）配置文件
            this.Config = this.GetConfig();

            //path of master
            textBox3.Text = this.Config["Base"]["MasterPath"];
            //path of online
            textBox4.Text = this.Config["Base"]["OnlinePath"];

            //默认commit日志
            textBox6.Text = this.Config["Base"]["UserName"] + "-" + DateTime.Now.ToString("yyyyMMdd(HH:mm:ss)") + "-";

            //按钮文字
            this.AssignButtonsText();

            this.AddLog("初始化git连接......");
            //初始化git连接
            if (this.Config["Base"]["IsInited"].Equals("N"))
            {
                if (this.InitGitConnection().Equals(String.Empty))
                {
                    //初始化成功，更新配置
                    this.SetConfig("Base", "IsInited", "Y");
                }
                else
                {
                    //初始化失败，禁用开始按钮
                    buttonGo.Enabled = false;
                    this.AddLog("[ERROR]git初始化失败，无法进行下一步操作");
                    return;
                }
            }

            this.AddLog("已准备就绪......");
        }

        /*初始化git连接*/
        private string InitGitConnection()
        {
            // C:\Users\dttx\.ssh 目录
            string SSHDir = @"C:\Users\" + Environment.UserName;
            // .ssh目录下的 known_hosts 文件
            string SSHHost = SSHDir + @"\known_hosts";

            // .ssh目录下的 id_rsa 文件
            //string SSHRsa = SSHDir + @"\id_rsa";

            // 程序安装目录下的 known_hosts 文件
            string MtoHost = Environment.CurrentDirectory + @"\known_hosts";


            if (Directory.Exists(SSHDir))
            {
                //目录已存在，检查文件 known_hosts 是否存在
                if (!File.Exists(SSHHost))
                {
                    try
                    {
                        // 将MTO提供的 known_hosts 文件移动（或复制）到 .ssh目录
                        File.Move(MtoHost, SSHHost);
                    }
                    catch (Exception e)
                    {
                        return e.Message;
                    }
                }

                /*
                // 检查文件 id_rsa 是否存在
                if (!File.Exists(SSHRsa)) {
                    try {
                        // 生成 IdRsa 文件
                        string IdRsa = this.GetRsaFile();
                        //复制到 .ssh目录
                        File.Move(IdRsa, SSHRsa);
                    } catch (Exception e) {
                        return e.Message;
                    }
                }
                */
            }
            else
            {
                try
                {
                    Directory.CreateDirectory(SSHDir);
                    //创建目录后，直接移动文件过去
                    File.Move(MtoHost, SSHHost);
                }
                catch (Exception e)
                {
                    return e.Message;
                }

                // 创建 id_rsa
                //...
            }

            return String.Empty;
        }

        /*个性化按钮文字*/
        private void AssignButtonsText()
        {
            foreach (Control control in flowLayoutPanel4.Controls)
            {
                Console.WriteLine(control.Name);
                if (this.Config["Button"].ContainsKey(control.Name))
                {
                    control.Text = this.Config["Button"][control.Name];
                }
            }
        }

        /*Ctr + A 全选功能*/
        private void textBox_controlA(object sender, KeyEventArgs e)
        {
            if (e.Control && e.KeyCode == Keys.A)
            {
                (sender as TextBox).SelectAll();
            }
        }

        /*回车键开始*/
        private void textBox_enter(object sender, KeyEventArgs e)
        {
            if (e.KeyCode == Keys.Enter)
            {
                button1_Click(sender, e);
            }
        }

        /*最小化*/
        private void bottonMin_Click(object sender, EventArgs e)
        {
            //this.Show();
            this.ShowInTaskbar = true;
            this.WindowState = FormWindowState.Minimized;
            //this.BringToFront();
        }

        /*关闭程序窗口*/
        private void buttonClose_Click(object sender, EventArgs e)
        {
            this.Close();
        }

        /*去吧皮卡丘（开始处理）*/
        private void button1_Click(object sender, EventArgs e)
        {
            //重新点击开始的时候
            if (!String.IsNullOrEmpty(textBox2.Text))
            {
                if (this.Config["Log"]["CleanOnRestart"] == "Y") textBox2.Clear();
            }

            this.AddLog("获取更新文件列表");
            //获取更新文件列表
            string[] FileList = this.getUpdateFileList();
            //Console.WriteLine(JsonConvert.SerializeObject(FileList));

            this.AddLog("检查是否有更新文件");
            //没有文件，不进行任何操作
            if (FileList.Length == 0) { MessageBox.Show("请在第一个文本框输入需要更新上线的文件，每行一个", "请输入更新文件", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            this.AddLog("检查文件路径格式是否正确");
            //检查文件路径格式是否正确
            string NotOK = IsAllFilesPathOK(ref FileList);
            if (!String.IsNullOrEmpty(NotOK)) { MessageBox.Show("文件路径格式错误：" + NotOK, "文件路径格式错误", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            this.AddLog("检查所有文件是否在同一个项目");
            //检查所有文件是否在同一个项目
            string app = IsAllFielsInSignleApp(ref FileList);
            if (app.IndexOf('/') != -1) { MessageBox.Show("每次操作只能更新一个项目，请移除不在同一个项目的文件：\n" + app, "请移除多余文件", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            //项目根目录（master）
            this.AppMasterPath = this.Config["Base"]["MasterPath"] + app;
            this.AddLog("设置项目master根目录：" + this.AppMasterPath);

            //项目根目录（master）
            this.AppOnlinePath = this.Config["Base"]["OnlinePath"] + app;
            this.AddLog("设置项目online根目录：" + this.AppOnlinePath);

            this.AddLog("检查是否填写了commit日志");
            //检查是否填写了commit日志
            if (String.IsNullOrEmpty(textBox6.Text)) { MessageBox.Show("请填写commit日志", "请填写commit日志", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            this.AddLog("检查commit日志是否符合格式");
            //检查commit日志是否符合格式
            if (!System.Text.RegularExpressions.Regex.IsMatch(textBox6.Text, @"^\w+-\d{8}\([\d:]+\)-.+$")) { MessageBox.Show("commit日志不符合格式，正确格式：\n叶良辰-20160731(32)-修改了功能1+删除了文件2+新增功能3", "commit日志不符合格式", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            //先全部拉取
            this.AddLog("拉取（git pull）master分支......");
            //git pull master
            string info = this.RunCmd(ref this.AppMasterPath, "git pull --no-squash --verbose --ff-only --progress");
            this.AddLog(info, false);
            if (IsEmpty(ref info)) { this.AddLog("git pull master失败......", false); return; }

            this.AddLog("检查文件是否全部存在（master）");
            //检查文件是否全部存在（master）
            string NotExists = this.IsAllFielsExists("MasterPath", ref FileList);
            if (!String.IsNullOrEmpty(NotExists)) { MessageBox.Show("文件不存在于master分支：" + this.Config["Base"]["MasterPath"] + NotExists, "文件不存在", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning); return; }

            this.AddLog("检查文件是否全部存在（online）...已跳过");
            /*
            //检查文件是否全部存在（online）
            NotExists = this.IsAllFielsExists("OnlinePath", FileList);
            if (!String.IsNullOrEmpty(NotExists)) { MessageBox.Show("文件不存在于online分支：" + this.Config["Base"]["OnlinePath"] + NotExists, "文件不存在", MessageBoxButtons.OKCancel, MessageBoxIcon.Warning);return; }
            */

            this.AddLog("拉取（git pull）online分支......");
            //git pull online
            info = this.RunCmd(ref this.AppOnlinePath, "git pull --no-squash --verbose --ff-only --progress");
            this.AddLog(info, false);
            if (IsEmpty(ref info)) { this.AddLog("git pull online失败......", false); return; }

            this.AddLog("从master复制文件到online......");
            //copy
            info = this.CopyFilesToOnline(ref FileList);
            this.AddLog(info);

            this.AddLog("online分支增加（git add）文件......");
            //git add online
            info = this.RunCmd(ref this.AppOnlinePath, "git add . --verbose && echo ok");
            this.AddLog(info, false);
            if (IsEmpty(ref info)) { this.AddLog("git add online失败......", false); return; }

            this.AddLog("提交（git commit）online分支......");
            //git commit online
            info = this.RunCmd(ref this.AppOnlinePath, "git commit --branch --verbose --message=\"" + textBox6.Text + '"');
            this.AddLog(info, false);
            if (IsEmpty(ref info)) { this.AddLog("git commit online失败......", false); return; }

            this.AddLog("推送（git push）online分支......");
            //git push online
            info = this.RunCmd(ref this.AppOnlinePath, "git push --porcelain --verbose --progress");
            this.AddLog(info, false);
            if (IsEmpty(ref info)) { this.AddLog("git push online失败......", false); return; }

            this.AddLog("处理Walle增量上线文件......");
            //显示walle上线文件
            textBox5.Text = String.Join(String.Empty, getFileListForWalle(ref FileList));
            //滚动到底部
            textBox5.SelectionStart = textBox5.Text.Length;
            textBox5.ScrollToCaret();
            textBox5.Refresh();
            this.AddLog("处理Walle增量上线文件......OK");

            this.AddLog("恭喜您，操作成功~~~");
        }

        //读取配置文件
        private Dictionary<string, Dictionary<string, string>> GetConfig()
        {
            //打开文件
            FileInfo file = new FileInfo(this.ConfigFlie);
            StreamReader sr = file.OpenText();
            //读取所有字符串
            string ConfigText = sr.ReadToEnd();
            sr.Close();
            return JsonConvert.DeserializeObject<Dictionary<string, Dictionary<string, string>>>(ConfigText);
        }

        /*设置配置项*/
        private void SetConfig(string group, string key, string val)
        {
            //更新配置项
            this.Config[group][key] = val;

            //写入文件
            FileInfo file = new FileInfo(this.ConfigFlie);
            FileStream fs = file.OpenWrite();
            //配置数据转化为JSON格式的字符
            Byte[] info = new UTF8Encoding(true).GetBytes(JsonConvert.SerializeObject(this.Config));

            //清空配置文件
            fs.Seek(0, SeekOrigin.Begin);
            fs.SetLength(0);
            //写入文件
            fs.Write(info, 0, info.Length);
            fs.Close();
        }

        /*获取所有待更新文件*/
        private string[] getUpdateFileList()
        {
            string files = textBox1.Text.Trim(new char[6] { '\r', '\n', '\t', '\\', '/', ' ' });
            if (String.IsNullOrEmpty(files)) return new string[0];

            //统一换行符
            files = files.Replace("\r\n", "\n");
            //统一目录分割符
            files = files.Replace('\\', '/');
            //去掉空行
            files = System.Text.RegularExpressions.Regex.Replace(files, @"\n\s+", "\n");
            //去掉行首的斜杠，如 /mob/users 改为 mob/users
            files = files.Replace("\n/", "\n");
            //切割成数组
            return files.Split('\n');
        }

        /*获取master项目文件夹*/
        private string GetMasterAppPath(String file)
        {
            return this.Config["Base"]["MasterPath"] + GetAppOfFile(file);
        }

        /*获取online项目文件夹*/
        private string GetOnlieAppPath(String file)
        {
            return this.Config["Base"]["OnliePath"] + GetAppOfFile(file);
        }

        /*检查文件列表是否全部存在*/
        private string IsAllFielsExists(string branch, ref string[] FileList)
        {
            string ok = String.Empty;
            foreach (string file in FileList)
            {
                if (!IsFileExists(this.Config["Base"][branch] + file))
                {
                    ok = file;
                    break;
                }
            }
            return ok;
        }

        /*复制master文件到online目录*/
        private string CopyFilesToOnline(ref string[] FileList)
        {
            try
            {
                foreach (string file in FileList)
                {
                    System.IO.File.Copy(this.Config["Base"]["MasterPath"] + file, this.Config["Base"]["OnlinePath"] + file, true);
                }
                return "文件复制成功~~~";
            }
            catch (Exception e)
            {
                return e.Message;
            }
        }

        /*显示操作过程日志*/
        private void AddLog(string msg, bool slash = true)
        {
            if (slash)
            {
                textBox2.Text = textBox2.Text + "———>" + msg + "\r\n";
            }
            else
            {
                textBox2.Text = textBox2.Text + msg + "\r\n";
            }

            //选中全部
            textBox2.SelectionStart = textBox2.Text.Length;
            //自动实时滚动到底部
            textBox2.ScrollToCaret();
            //刷新显示
            textBox2.Refresh();

        }

        /*获取git目录*/
        static string GetGitDir()
        {
            //git所在目录
            string git = String.Empty;
            //环境变量PATH，按目录切割成数组
            string[] path = System.Environment.GetEnvironmentVariable("PATH").Split(';');

            foreach (string dir in path)
            {
                if (dir.IndexOf(@"\Git\cmd") != -1)
                {
                    //d:\Git\cmd 改为 d:\Git\git-cmd.exe
                    git = dir.Replace(@"\cmd", @"\git-cmd.exe");
                    break;
                }
            }
            return git;
        }

        /*格式化文件列表为walle上线文件*/
        static string[] getFileListForWalle(ref string[] FileList)
        {
            int i = FileList.Length;
            int loop = i;
            while (Convert.ToBoolean(loop--))
            {
                FileList[loop] = FileList[loop].Substring(FileList[loop].IndexOf('/') + 1) + "\r\n";
            }

            //最后一个不要换行符
            FileList[i - 1] = FileList[i - 1].Substring(0, FileList[i - 1].Length - 2);

            return FileList;
        }

        /*检查是否所有文件都在同一个项目*/
        static string IsAllFielsInSignleApp(ref string[] FileList)
        {
            //上一个文件所在的项目
            string last = GetAppOfFile(FileList[0]);
            //当前文件所在的项目
            string now = "";
            //最后返回项目名称d
            string app = "";
            foreach (string file in FileList)
            {
                app = now = GetAppOfFile(file);
                if (now != last)
                {
                    app = file;
                    break;
                }
                last = now;
            }

            return app;
        }

        /*检查所有文件格式是否正确*/
        static string IsAllFilesPathOK(ref string[] FileList)
        {
            string ok = String.Empty;
            foreach (string file in FileList)
            {
                if (file.IndexOf('/') == -1 && file.IndexOf('\\') == -1)
                {
                    ok = file;
                    break;
                }
            }
            return ok;
        }


        /*获取文件所在的项目*/
        static string GetAppOfFile(string file)
        {
            return file.Substring(0, file.IndexOf('/'));
        }

        /*检查一个文件是否存在*/
        static Boolean IsFileExists(string FileName)
        {
            return new FileInfo(FileName).Exists;
        }

        /*检查一个目录是否存在*/
        static Boolean IsDirectoryExists(ref string DirectoryName)
        {
            return new DirectoryInfo(DirectoryName).Exists;
        }

        /*输出配置数据*/
        static void LoopDictionary(Dictionary<string, Dictionary<string, string>> data)
        {
            foreach (KeyValuePair<string, Dictionary<string, string>> dict in data)
            {
                foreach (KeyValuePair<string, string> kv in dict.Value)
                {
                    Console.WriteLine(kv.Key + '：' + kv.Value);
                }
            }
        }

        /*检查cmd输出是否为空*/
        static Boolean IsEmpty(ref string output)
        {
            return String.IsNullOrWhiteSpace(output.Substring(output.IndexOf("&exit") + 5));
        }

        /*通过cmd运行命令*/
        private string RunCmd(ref string path, string cmd)
        {
            try
            {
                Process p = new Process();
                //p.StartInfo.FileName = @"C:\Windows\System32\cmd.exe";
                p.StartInfo.FileName = this.CmdDir;
                p.StartInfo.UseShellExecute = false;        //是否使用操作系统shell启动
                p.StartInfo.RedirectStandardInput = true;   //接受来自调用程序的输入信息
                p.StartInfo.RedirectStandardOutput = true;  //由调用程序获取输出信息
                p.StartInfo.RedirectStandardError = true;   //重定向标准错误输出
                p.StartInfo.CreateNoWindow = this.Config["Log"]["ShowCmdWindow"] == "N"; //显示程序窗口
                p.Start();//启动程序

                //进入目标磁盘 + 进入目标目录
                p.StandardInput.WriteLine(path.Substring(0, 2) + " && " + " cd " + path + " && " + cmd + " &exit");
                p.StandardInput.AutoFlush = true;

                //获取cmd窗口的输出信息
                string output = p.StandardOutput.ReadToEnd();
                //等待程序执行完退出进程
                p.WaitForExit();
                p.Close();

                return output;

            }
            catch (Exception e)
            {
                MessageBox.Show(e.Message);
                return String.Empty;
            }
        }
    }
}