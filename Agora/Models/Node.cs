namespace Agora.Models
{
    public class Node
    {
        public Guid? IdNode { get; set; } = Guid.NewGuid();
        public Guid IdTemplate { get; set; }
        public String Name { get; set; } = "";
        public String JsonValue { get; set; } = "";
        public DateTime? ModifyDate { get; set; } = DateTime.UtcNow;

    }
    public class NodeRoot:Node
    {
        public String NameTemplate { get; set; } = "";
    }
    public class NodeList:NodeRoot
    {
        public Guid? IdNodeParent { get; set; }
        public int Place { get; set; } = 0;
    }

    public class NodeRelation
    {
        public int IdRelation { get; set; }
        public int? Place { get; set; }
        public Guid IdNode { get; set; }
        public Guid IdNodeParent { get; set; }
        public Guid IdNodeRoot { get; set; }
    }
}