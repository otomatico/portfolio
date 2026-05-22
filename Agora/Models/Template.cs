namespace Agora.Models
{
    public class Template
    {
        public Guid? IdTemplate { get; set; } = Guid.NewGuid();
        public String Name { get; set; } = "";
        public DateTime CreateDate { get; set; } = DateTime.UtcNow;
        public DateTime ModifyDate { get; set; } = DateTime.UtcNow;

    }
    public class TemplateField
    {
        public Guid? IdTemplate { get; set; } = Guid.NewGuid();
        public Guid? IdField { get; set; } = Guid.NewGuid();
        public Guid? IdMaster { get; set; } = Guid.NewGuid();
        public int IdType { get; set; } = 3;
        public string Name { get; set; } = "";
        public string AttributeName { get; set; } = "";
        public string? DefaultValue { get; set; } = "";
        public bool Required { get; set; } = false;
        public int Place { get; set; } = 0;

    }
    public class TemplateAllowedChildren
    {
        public Guid IdTemplate { get; set; } = Guid.NewGuid();
        public Guid IdTemplateParent { get; set; } = Guid.NewGuid();
        public int MaxAllowed { get; set; } = 0;

    }
}