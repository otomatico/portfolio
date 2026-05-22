namespace Agora.Models
{
    public class Master
    {
        public Guid? IdMaster { get; set; } = Guid.NewGuid();
        public String Name { get; set; } = "";
        public DateTime CreateDate { get; set; } = DateTime.UtcNow;
        public DateTime? ModifyDate { get; set; } = DateTime.UtcNow;

    }
    public class MasterOption
    {
        public Guid? IdMaster { get; set; } = Guid.NewGuid();
        public Guid? IdOption { get; set; } = Guid.NewGuid();
        public string Name { get; set; } = "";
        public string Value { get; set; } = "";
        public int Place { get; set; } = 0;

    }
}